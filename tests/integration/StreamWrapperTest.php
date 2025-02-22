<?php
/**
 * Integration tests for S3 Media Sync stream wrapper
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync;
use S3_Media_Sync\Tests\TestCase;
use PHPUnit\Framework\Assert;
use Aws\S3\S3Client;
use Mockery;
use Aws\Result;
use Aws\Command;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

/**
 * Test case for S3 Media Sync stream wrapper functionality.
 *
 * @group integration
 * @group stream-wrapper
 * @covers S3_Media_Sync
 */
class StreamWrapperTest extends TestCase {

	/**
	 * Test data for stream wrapper registration scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_stream_wrapper_settings(): array {
		return [
			'complete settings' => [
				[
					'bucket'     => 'test-bucket',
					'key'        => 'test-key',
					'secret'     => 'test-secret',
					'region'     => 'test-region',
					'object_acl' => 'public-read',
				],
			],
		];
	}

	/**
	 * Test stream wrapper registration.
	 *
	 * @dataProvider data_provider_stream_wrapper_settings
	 * 
	 * @param array $settings The settings to test with.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_stream_wrapper_registered( array $settings ): void {
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this->s3_media_sync->setup();

		Assert::assertContains( 's3', stream_get_wrappers() );
	}

	/**
	 * Test stream wrapper functionality with mock S3 client.
	 *
	 * @dataProvider data_provider_stream_wrapper_settings
	 * 
	 * @param array $settings The settings to test with.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_stream_wrapper_functionality( array $settings ): void {
		// Create a mock command.
		$mock_command = Mockery::mock( Command::class );
		$mock_command->shouldReceive( 'offsetGet' )
			->with( Mockery::any() )
			->andReturnUsing( function ( $offset ) {
				$data = [
					'Bucket' => 'test-bucket',
					'Key'    => 'test.txt',
					'Body'   => 'Test content',
				];
				return $data[ $offset ] ?? null;
			} );
		$mock_command->shouldReceive( 'offsetExists' )
			->with( Mockery::any() )
			->andReturnUsing( function ( $offset ) {
				$data = [
					'Bucket' => 'test-bucket',
					'Key'    => 'test.txt',
					'Body'   => 'Test content',
				];
				return isset( $data[ $offset ] );
			} );
		$mock_command->shouldReceive( 'offsetSet' )
			->with( Mockery::any(), Mockery::any() );
		$mock_command->shouldReceive( 'offsetUnset' )
			->with( Mockery::any() );

		// Create a mock S3 client.
		$mock_s3_client = Mockery::mock( S3Client::class );

		// Mock bucket existence check.
		$mock_s3_client->shouldReceive( 'doesBucketExist' )
			->with( $settings['bucket'] )
			->andReturn( true );

		// Mock putObject for writing.
		$mock_s3_client->shouldReceive( 'putObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] ) &&
					isset( $args['Body'] );
			} )
			->andReturn( new Result( [] ) );

		// Mock getObject for reading.
		$mock_s3_client->shouldReceive( 'getObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturn( new Result( [
				'Body' => \GuzzleHttp\Psr7\Utils::streamFor( 'Test content' ),
			] ) );

		// Mock headObject for file existence check.
		$mock_s3_client->shouldReceive( 'headObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturnUsing( function () use ( &$file_exists ) {
				if ( isset( $file_exists ) && ! $file_exists ) {
					throw new \Aws\S3\Exception\S3Exception( 'File not found', new Command( 'headObject' ) );
				}
				return new Result( [] );
			} );

		// Mock deleteObject for file deletion.
		$mock_s3_client->shouldReceive( 'deleteObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturnUsing( function () use ( &$file_exists ) {
				$file_exists = false;
				return new Result( [] );
			} );

		// Mock getCommand for internal AWS SDK usage.
		$mock_s3_client->shouldReceive( 'getCommand' )
			->withArgs( function ( $command, $args ) use ( $settings ) {
				return in_array( $command, [ 'PutObject', 'GetObject', 'HeadObject', 'DeleteObject' ], true ) &&
					$args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturn( $mock_command );

		// Mock execute method for internal AWS SDK usage.
		$mock_s3_client->shouldReceive( 'execute' )
			->with( Mockery::type( Command::class ) )
			->andReturn( new Result( [
				'Body' => \GuzzleHttp\Psr7\Utils::streamFor( 'Test content' ),
			] ) );

		// Set up the plugin with mock client.
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$mock_s3_client
		);

		$this->s3_media_sync->setup();

		// Test file operations.
		$test_file = 'test.txt';
		$test_content = 'Test content';
		$s3_path = 's3://' . $settings['bucket'] . '/' . $test_file;

		// Test writing to S3.
		$stream = fopen( $s3_path, 'w' );
		Assert::assertIsResource( $stream, 'Failed to open S3 stream for writing' );
		
		$bytes_written = fwrite( $stream, $test_content );
		Assert::assertSame( strlen( $test_content ), $bytes_written, 'Failed to write to S3 stream' );
		
		fclose( $stream );

		// Test reading from S3.
		$stream = fopen( $s3_path, 'r' );
		Assert::assertIsResource( $stream, 'Failed to open S3 stream for reading' );
		
		$content = fread( $stream, strlen( $test_content ) );
		Assert::assertSame( $test_content, $content, 'Failed to read from S3 stream' );
		
		fclose( $stream );

		// Test file existence.
		Assert::assertTrue( file_exists( $s3_path ), 'File should exist in S3' );

		// Test file deletion.
		Assert::assertTrue( unlink( $s3_path ), 'Failed to delete file from S3' );
		Assert::assertFalse( file_exists( $s3_path ), 'File should not exist in S3 after deletion' );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
