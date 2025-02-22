<?php
/**
 * Integration tests for S3 Media Sync media upload functionality
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

/**
 * Test case for S3 Media Sync media upload functionality.
 *
 * @group integration
 * @group media-upload
 * @covers S3_Media_Sync
 */
class MediaUploadTest extends TestCase {

	/**
	 * Test data for media upload scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_media_uploads(): array {
		$upload_dir = wp_upload_dir();
		$year_month = date( 'Y/m' );
		
		return [
			'image upload' => [
				[
					'file' => [
						'name' => 'test-image.jpg',
						'type' => 'image/jpeg',
						'tmp_name' => '/tmp/test-image.jpg',
						'error' => 0,
						'size' => 1024,
					],
					'expected_s3_path' => 'wp-content/uploads/' . $year_month . '/test-image.jpg',
				],
			],
			'document upload' => [
				[
					'file' => [
						'name' => 'test-doc.pdf',
						'type' => 'application/pdf',
						'tmp_name' => '/tmp/test-doc.pdf',
						'error' => 0,
						'size' => 2048,
					],
					'expected_s3_path' => 'wp-content/uploads/' . $year_month . '/test-doc.pdf',
				],
			],
		];
	}

	/**
	 * Test media upload synchronization with S3.
	 *
	 * @dataProvider data_provider_media_uploads
	 * 
	 * @param array $test_data The test data.
	 */
	public function test_media_upload_syncs_to_s3( array $test_data ): void {
		$settings = [
			'bucket'     => 'test-bucket',
			'key'        => 'test-key',
			'secret'     => 'test-secret',
			'region'     => 'test-region',
			'object_acl' => 'public-read',
		];

		// Create a mock S3 client.
		$mock_s3_client = Mockery::mock( S3Client::class );

		// Mock bucket existence check.
		$mock_s3_client->shouldReceive( 'doesBucketExist' )
			->with( $settings['bucket'] )
			->andReturn( true );

		// Mock putObject for the upload.
		$mock_s3_client->shouldReceive( 'putObject' )
			->withArgs( function ( $args ) use ( $settings, $test_data ) {
				return $args['Bucket'] === $settings['bucket'] &&
					$args['Key'] === $test_data['expected_s3_path'] &&
					isset( $args['Body'] );
			} )
			->twice()
			->andReturn( new Result( [] ) );

		// Mock stream wrapper methods
		$mock_s3_client->shouldReceive( 'getCommand' )
			->withArgs( function ( $command, $args ) use ( $settings ) {
				return in_array( $command, [ 'PutObject', 'GetObject', 'HeadObject', 'DeleteObject' ], true ) &&
					$args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturn( Mockery::mock( [ 'offsetGet' => null, 'offsetExists' => false ] ) );

		$mock_s3_client->shouldReceive( 'execute' )
			->withArgs( function ( $command ) {
				return true;
			} )
			->andReturn( new Result( [] ) );

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

		// Create a temporary test file.
		$upload_dir = wp_upload_dir();
		$test_file_path = $upload_dir['path'] . '/' . basename( $test_data['file']['name'] );
		file_put_contents( $test_file_path, 'Test content' );

		// Simulate WordPress upload.
		$upload = [
			'file' => $test_file_path,
			'url'  => $upload_dir['url'] . '/' . basename( $test_data['file']['name'] ),
			'type' => $test_data['file']['type'],
		];

		// Test the upload sync.
		$result = $this->s3_media_sync->add_attachment_to_s3( $upload, 'upload' );

		// Verify the upload was processed.
		Assert::assertSame( $upload, $result, 'Upload data should be returned unchanged' );

		// Clean up.
		unlink( $test_file_path );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
