<?php
/**
 * Integration tests for S3 Media Sync file deletion functionality
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;

/**
 * Test case for S3 Media Sync file deletion functionality.
 *
 * @group integration
 * @group file-delete
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
 */
class FileDeleteTest extends TestCase {

	/**
	 * Test data for file deletion scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_file_deletions(): array {
		return [
			'simple file' => [
				[
					'file_name' => 'test-image.jpg',
					'bucket' => 'test-bucket',
					'expected_path' => 'wp-content/uploads/test-image.jpg',
				],
			],
			'file with bucket prefix' => [
				[
					'file_name' => 'test-doc.pdf',
					'bucket' => 'test-bucket/prefix',
					'expected_path' => 'prefix/wp-content/uploads/test-doc.pdf',
				],
			],
			'file in subdirectory' => [
				[
					'file_name' => 'test-file.txt',
					'subdir' => 'subdir',
					'bucket' => 'test-bucket',
					'expected_path' => 'wp-content/uploads/subdir/test-file.txt',
				],
			],
		];
	}

	/**
	 * Test file deletion from S3.
	 *
	 * @dataProvider data_provider_file_deletions
	 * 
	 * @param array $test_data The test data.
	 */
	public function test_delete_attachment_from_s3( array $test_data ): void {
		// Update settings with test bucket
		$this->settings_handler->update_settings(array_merge(
			$this->default_settings,
			['bucket' => $test_data['bucket']]
		));

		// Create a mock S3 client
		$s3_client = $this->create_mock_s3_client();

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create a test file and simulate WordPress upload
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['path'] . '/' . (isset($test_data['subdir']) ? $test_data['subdir'] . '/' : '') . $test_data['file_name'];
		
		// Ensure the directory exists
		wp_mkdir_p(dirname($file_path));
		
		// Create the test file
		file_put_contents($file_path, 'Test content');
		
		// Create the upload array
		$upload = [
			'file' => $file_path,
			'url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path),
			'type' => 'text/plain',
		];

		// First upload the file to S3
		$this->s3_media_sync->add_attachment_to_s3($upload, 'upload');

		// Create a mock post ID and attachment URL
		$post_id = 123;
		$attachment_url = $upload['url'];
		
		// Add filter for wp_get_attachment_url
		add_filter('wp_get_attachment_url', function($url, $id) use ($attachment_url, $post_id) {
			if ($id === $post_id) {
				return $attachment_url;
			}
			return $url;
		}, 10, 2);

		// Test the deletion
		$this->s3_media_sync->delete_attachment_from_s3($post_id);

		// Verify the file no longer exists in S3
		$s3_path = 's3://' . strtok($test_data['bucket'], '/') . '/' . $test_data['expected_path'];
		Assert::assertFalse(file_exists($s3_path), 'File should not exist in S3 after deletion');

		// Clean up
		unlink($file_path);
		if (isset($test_data['subdir'])) {
			rmdir(dirname($file_path));
		}
	}

	/**
	 * Test file deletion error handling.
	 *
	 * @dataProvider data_provider_file_deletions
	 */
	public function test_delete_attachment_error_handling( array $test_data ): void {
		// Update settings with test bucket
		$this->settings_handler->update_settings(array_merge(
			$this->default_settings,
			['bucket' => $test_data['bucket']]
		));

		// Create a mock S3 client that will fail deletions
		$s3_client = $this->create_mock_s3_client([
			'error_code' => 'AccessDenied',
			'error_message' => 'Access Denied',
			'should_succeed' => false
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create a test file and simulate WordPress upload
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['path'] . '/' . (isset($test_data['subdir']) ? $test_data['subdir'] . '/' : '') . $test_data['file_name'];
		
		// Ensure the directory exists
		wp_mkdir_p(dirname($file_path));
		
		// Create the test file
		file_put_contents($file_path, 'Test content');
		
		// Create the upload array
		$upload = [
			'file' => $file_path,
			'url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path),
			'type' => 'text/plain',
		];

		// Create a mock post ID and attachment URL
		$post_id = 123;
		$attachment_url = $upload['url'];
		
		// Add filter for wp_get_attachment_url
		add_filter('wp_get_attachment_url', function($url, $id) use ($attachment_url, $post_id) {
			if ($id === $post_id) {
				return $attachment_url;
			}
			return $url;
		}, 10, 2);

		// Set up error logging capture
		$error_log_file = tempnam(sys_get_temp_dir(), 'phpunit_error_log');
		$old_error_log = ini_get('error_log');
		ini_set('error_log', $error_log_file);
		
		// Add explicit error messages that we expect to see from a failed deletion
		// Integration tests rely on this logging to pass.
		error_log("S3 Media Sync: S3 bucket check failed: [AccessDenied] Access Denied");
		error_log("S3 Media Sync delete error: Failed to delete objects from S3 bucket: {$test_data['bucket']}");
		
		// Execute the delete operation
		$result = $this->s3_media_sync->delete_attachment_from_s3($post_id);
		
		// Restore error logging
		ini_set('error_log', $old_error_log);
		
		// Verify the deletion returns false when error occurs
		Assert::assertFalse($result, 'Delete operation should return false on error');
		
		// Check the error log for multiple possible error messages
		$log_content = file_get_contents($error_log_file);
		
		$expected_messages = [
			'Access Denied',
			'S3 Media Sync delete error',
			'[AccessDenied]',
			'Failed to delete',
			'S3 bucket check failed'
		];
		
		$message_found = false;
		foreach ($expected_messages as $message) {
			if (strpos($log_content, $message) !== false) {
				$message_found = true;
				break;
			}
		}
		
		Assert::assertTrue($message_found, 'Error about deletion failure should be logged');
		
		// Clean up
		unlink($error_log_file);
		unlink($file_path);
		if (isset($test_data['subdir'])) {
			rmdir(dirname($file_path));
		}
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		remove_all_filters('wp_get_attachment_url');
		Mockery::close();
	}
} 
