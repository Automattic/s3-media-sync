<?php
/**
 * Integration tests for S3 Media Sync stream wrapper
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Utils;
use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;

/**
 * Test case for S3 Media Sync stream wrapper functionality.
 *
 * @group integration
 * @group stream-wrapper
 * @covers \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
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
		// Set up the plugin with mock client.
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		// Track file existence state
		$file_exists = false;

		// Create a custom handler for the mock S3 client
		$handler = function ($command, $request) use (&$file_exists) {
			if ($command->getName() === 'DeleteObject') {
				$file_exists = false;
				return Promise\Create::promiseFor(new Result([]));
			} elseif ($command->getName() === 'HeadObject') {
				if (!$file_exists) {
					return Promise\Create::rejectionFor(
						new S3Exception(
							'Not Found',
							$command,
							['code' => 'NoSuchKey']
						)
					);
				}
				return Promise\Create::promiseFor(new Result(['ContentLength' => 11]));
			} elseif ($command->getName() === 'PutObject') {
				$file_exists = true;
				return Promise\Create::promiseFor(new Result([]));
			}
			return Promise\Create::promiseFor(new Result(['Body' => Utils::streamFor('Test content')]));
		};

		$s3_client = new S3Client([
			'version' => 'latest',
			'region' => $settings['region'],
			'credentials' => [
				'key' => $settings['key'],
				'secret' => $settings['secret']
			],
			'handler' => $handler,
			'use_aws_shared_config_files' => false,
			'endpoint' => 'http://localhost',
			'validate' => false
		]);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
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
