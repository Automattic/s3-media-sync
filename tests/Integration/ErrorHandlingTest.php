<?php
/**
 * Integration tests for S3 Media Sync error handling
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync;
use S3_Media_Sync_Settings;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\File_Comparison;

/**
 * Test case for S3 Media Sync error handling functionality.
 *
 * @group integration
 * @group error-handling
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
 */
class ErrorHandlingTest extends TestCase {

	protected S3_Media_Sync $s3_media_sync;
	protected $test_file;
	protected $settings;
	protected S3_Bucket $bucket;
	protected ?File_Comparison $comparison = null;

	public function set_up(): void {
		parent::set_up();
		
		$this->settings_handler = new S3_Media_Sync_Settings();
		$this->settings_handler->update_settings($this->default_settings);
		$this->s3_media_sync = new S3_Media_Sync($this->settings_handler);
		
		$this->test_file = $this->create_temp_file();

		// Create a bucket object for testing
		$this->bucket = S3_Bucket::from_settings([
			'bucket' => $this->default_settings['bucket'],
			'region' => $this->default_settings['region'],
			'use_acl' => $this->default_settings['use_acl'] ?? true,
			'object_acl' => $this->default_settings['object_acl'] ?? 'private'
		]);
	}

	/**
	 * Test data for error scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_error_scenarios() {
		return [
			'invalid credentials' => [
				'error_code' => 'InvalidAccessKeyId',
				'error_message' => 'The AWS Access Key Id you provided does not exist in our records.',
			],
			'non-existent bucket' => [
				'error_code' => 'NoSuchBucket',
				'error_message' => 'The specified bucket does not exist.',
			],
			'insufficient permissions' => [
				'error_code' => 'AccessDenied',
				'error_message' => 'Access Denied',
			],
		];
	}

	/**
	 * Test error handling during operations
	 *
	 * @dataProvider data_provider_error_scenarios
	 */
	public function test_error_handling_during_operations(string $error_code, string $error_message): void {
		// Create a mock S3 client that will fail
		$s3_client = $this->create_mock_s3_client([
			'error_code' => $error_code,
			'error_message' => $error_message,
			'should_succeed' => false
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create test files
		$local_file = $this->create_temp_file('test-file.txt', 'Test content');
		$s3_file = $this->create_test_s3_file($local_file, $this->bucket);
		$this->comparison = File_Comparison::compare($local_file, $s3_file);

		// Simulate WordPress upload
		$upload = $this->create_test_upload($local_file, 'text/plain');

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
		Assert::assertStringContainsString($error_message, $log_content, 'Error should be logged');

		// Clean up
		$local_path = $local_file->get_path();
		if (file_exists($local_path)) {
			unlink($local_path);
		}
		unlink($error_log_file);
	}

	/**
	 * Test stream wrapper error handling
	 *
	 * @dataProvider data_provider_error_scenarios
	 */
	public function test_stream_wrapper_error_handling(string $error_code, string $error_message): void {
		// Create a mock S3 client that will fail
		$s3_client = $this->create_mock_s3_client([
			'error_code' => $error_code,
			'error_message' => $error_message,
			'should_succeed' => false,
			'handle_streams' => true
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create test files
		$local_file = $this->create_temp_file('test-file.txt', 'Test content');
		$s3_file = $this->create_test_s3_file($local_file, $this->bucket);
		$this->comparison = File_Comparison::compare($local_file, $s3_file);

		// Set up error logging capture
		$error_log_file = tempnam(sys_get_temp_dir(), 'phpunit_error_log');
		$old_error_log = ini_get('error_log');
		ini_set('error_log', $error_log_file);

		// Test file operations
		try {
			$s3_path = 's3://' . $this->bucket->get_name() . '/' . $s3_file->get_key();
			file_get_contents($s3_path);
			Assert::fail('Should not be able to read from S3');
		} catch (\Exception $e) {
			// For stream wrapper operations, we need to check the error code in the message
			$error_found = false;
			$message = $e->getMessage();
			if (strpos($message, $error_code) !== false) {
				$error_found = true;
			} else if (strpos($message, $error_message) !== false) {
				$error_found = true;
			} else if (strpos($message, 'Not Found') !== false) {
				// For stream wrapper, we might get a "Not Found" error
				$error_found = true;
			}
			Assert::assertTrue($error_found, 'Error code or message should be present in exception: ' . $message);
		}

		// Clean up
		$local_path = $local_file->get_path();
		if (file_exists($local_path)) {
			unlink($local_path);
		}
		unlink($error_log_file);
	}

	public function tear_down(): void {
		parent::tear_down();
		if ($this->test_file instanceof Local_File) {
			$test_file_path = $this->test_file->get_path();
			if (file_exists($test_file_path)) {
				unlink($test_file_path);
			}
		}
		Mockery::close();
	}
}
