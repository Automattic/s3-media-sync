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
use S3_Media_Sync\Value_Objects\S3_Bucket;

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
					'region'     => 'us-east-1',
					'use_acl'    => true,
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
	 */
	public function test_stream_wrapper_registered( array $settings ): void {
		$this->settings_handler->update_settings($settings);
		$this->s3_media_sync->setup();

		Assert::assertContains( 's3', stream_get_wrappers(), 'S3 stream wrapper should be registered' );
	}

	/**
	 * Test stream wrapper functionality with mock S3 client.
	 *
	 * @dataProvider data_provider_stream_wrapper_settings
	 * 
	 * @param array $settings The settings to test with.
	 */
	public function test_stream_wrapper_functionality( array $settings ): void {
		// Set up the plugin with mock client.
		$this->settings_handler->update_settings($settings);
		$this->create_mock_s3_client();
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
	 * Test registering the stream wrapper.
	 */
	public function test_register_stream_wrapper(): void {
		$settings = [
			'bucket'     => 'test-bucket',
			'key'       => 'test-key',
			'secret'    => 'test-secret',
			'region'    => 'us-east-1',
			'use_acl'   => true,
			'object_acl' => 'public-read',
		];

		$bucket = S3_Bucket::from_settings($settings);
		$client = new S3Client([
			'version' => 'latest',
			'region'  => $bucket->get_region()->get_identifier(),
			'credentials' => [
				'key'    => $settings['key'],
				'secret' => $settings['secret'],
			],
		]);

		\S3_Media_Sync_Stream_Wrapper::register($client);
		$context = stream_context_get_default();
		$s3_options = stream_context_get_options($context)['s3'] ?? [];

		Assert::assertContains('s3', stream_get_wrappers(), 'S3 stream wrapper should be registered');
		Assert::assertSame($bucket->get_acl(), $s3_options['ACL'], 'Stream wrapper should have correct ACL');
		Assert::assertTrue($s3_options['seekable'], 'Stream wrapper should be seekable');
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		if (in_array('s3', stream_get_wrappers(), true)) {
			stream_wrapper_unregister('s3');
		}
		Mockery::close();
	}
} 
