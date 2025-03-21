<?php
/**
 * Integration tests for S3 Media Sync media upload functionality
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;

/**
 * Test case for S3 Media Sync media upload functionality.
 *
 * @group integration
 * @group media-upload
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 */
class MediaUploadTest extends TestCase {

	protected S3_Bucket $bucket;

	public function set_up(): void {
		parent::set_up();

		// Create a bucket object for testing
		$this->bucket = S3_Bucket::from_settings([
			'bucket' => $this->default_settings['bucket'],
			'region' => $this->default_settings['region'],
			'use_acl' => $this->default_settings['use_acl'] ?? true,
			'object_acl' => $this->default_settings['object_acl'] ?? 'private'
		]);
	}

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
	 * Test that media uploads sync to S3
	 *
	 * @dataProvider data_provider_media_uploads
	 */
	public function test_media_upload_syncs_to_s3(array $test_data): void {
		// Create a mock S3 client that will handle file operations
		$uploaded_keys = [];
		$s3_client = $this->create_mock_s3_client([
			'should_succeed' => true,
			'handle_streams' => true,
			'debug_callback' => function($operation, $args) use (&$uploaded_keys) {
				if ($operation === 'putObject') {
					$uploaded_keys[] = $args['Key'];
				}
			}
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create a test file
		$local_file = $this->create_temp_file($test_data['file']['name'], 'Test content');
		$file_path = $local_file->get_path();

		// Create the S3 file object
		$s3_file = $this->create_test_s3_file($local_file, $this->bucket);
		$s3_path = 's3://' . $this->bucket->get_name() . '/' . $s3_file->get_key();

		// Simulate WordPress upload
		$upload = $this->create_test_upload($local_file, $test_data['file']['type']);

		// Upload to S3
		$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
		Assert::assertSame($upload, $result, 'Upload data should be returned unchanged');

		// Verify the file exists in S3
		$s3_exists = file_exists($s3_path);
		Assert::assertTrue($s3_exists, 'File should exist in S3');

		// Verify the content was uploaded correctly
		$s3_content = file_get_contents($s3_path);
		Assert::assertSame('Test content', $s3_content, 'S3 content should match');

		// Clean up
		unlink($file_path);
	}

	/**
	 * Test error handling during media upload
	 *
	 * @dataProvider data_provider_media_uploads
	 */
	public function test_media_upload_error_handling(array $test_data): void {
		// Create a mock S3 client that will fail
		$s3_client = $this->create_mock_s3_client([
			'error_code' => 'AccessDenied',
			'error_message' => 'Access Denied',
			'should_succeed' => false
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create a test file
		$local_file = $this->create_temp_file($test_data['file']['name'], 'Test content');
		$file_path = $local_file->get_path();

		// Simulate WordPress upload
		$upload = $this->create_test_upload($local_file, $test_data['file']['type']);

		// Set up error logging capture
		$error_log_file = tempnam(sys_get_temp_dir(), 'phpunit_error_log');
		$old_error_log = ini_get('error_log');
		ini_set('error_log', $error_log_file);

		// Run the test - this should log the error but still return the upload data
		$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');

		// Restore error logging
		ini_set('error_log', $old_error_log);

		// Verify the result is still the original upload data
		Assert::assertSame($upload, $result, 'Upload data should be returned unchanged even on error');

		// Check the error log
		$log_content = file_get_contents($error_log_file);
		Assert::assertStringContainsString('Access Denied', $log_content, 'Error should be logged');

		// Clean up
		unlink($error_log_file);
		unlink($file_path);
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
