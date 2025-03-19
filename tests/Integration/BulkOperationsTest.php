<?php
/**
 * Integration tests for S3 Media Sync bulk operations
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;

/**
 * Test case for S3 Media Sync bulk operations functionality.
 *
 * @group integration
 * @group bulk-operations
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
 */
class BulkOperationsTest extends TestCase {

	/**
	 * Test data for bulk upload scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_bulk_operations(): array {
		$upload_dir = wp_upload_dir();
		$year_month = date( 'Y/m' );
		$base_url = wp_parse_url( $upload_dir['url'] )['path'];
		
		return [
			'multiple images' => [
				[
					'files' => [
						[
							'name' => 'test-image-1.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image-1.jpg',
							'url' => $upload_dir['url'] . '/test-image-1.jpg',
						],
						[
							'name' => 'test-image-2.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image-2.jpg',
							'url' => $upload_dir['url'] . '/test-image-2.jpg',
						],
					],
				],
			],
			'mixed file types' => [
				[
					'files' => [
						[
							'name' => 'test-doc.pdf',
							'type' => 'application/pdf',
							'path' => $upload_dir['path'] . '/test-doc.pdf',
							'url' => $upload_dir['url'] . '/test-doc.pdf',
						],
						[
							'name' => 'test-image.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image.jpg',
							'url' => $upload_dir['url'] . '/test-image.jpg',
						],
					],
				],
			],
		];
	}

	/**
	 * Test bulk upload synchronization with S3.
	 *
	 * @dataProvider data_provider_bulk_operations
	 * 
	 * @param array $test_data The test data.
	 */
	public function test_bulk_upload_syncs_to_s3( array $test_data ): void {
		// Create a mock S3 client
		$s3_client = $this->create_mock_s3_client();

		// Set up the plugin
		$this->s3_media_sync->setup();

		$test_files = [];

		// Create temporary test files and process them.
		foreach ( $test_data['files'] as $file ) {
			// Create the test file with unique content
			$test_content = 'Test content for ' . $file['name'];
			$test_file_path = $this->create_temp_file($file['name'], $test_content);
			$test_files[$file['name']] = [
				'path' => $test_file_path,
				'content' => $test_content
			];

			// Simulate WordPress upload
			$upload = $this->create_test_upload($test_file_path, $file['type']);

			// Test the upload sync
			$result = $this->s3_media_sync->add_attachment_to_s3( $upload, 'upload' );

			// Verify the upload was processed
			Assert::assertSame( $upload, $result, 'Upload data should be returned unchanged for ' . $file['name'] );

			// Get the uploads dir info
			$uploads = wp_upload_dir();
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $upload['file']);
			$expected_s3_path = 'wp-content/uploads/' . $file_subpath;
			
			// Verify the file exists in S3 with the new path structure
			$s3_path = 's3://' . $this->default_settings['bucket'] . '/' . $expected_s3_path;
			Assert::assertTrue(file_exists($s3_path), 'File should exist in S3: ' . $file['name']);

			// Verify the content was uploaded correctly
			$s3_content = file_get_contents($s3_path);
			Assert::assertSame($test_content, $s3_content, 'S3 content should match for ' . $file['name']);
		}

		// Clean up test files
		foreach ($test_files as $file) {
			unlink($file['path']);
		}
	}

	/**
	 * Test bulk upload error handling.
	 *
	 * @dataProvider data_provider_bulk_operations
	 */
	public function test_bulk_upload_error_handling( array $test_data ): void {
		// Create a mock S3 client that will fail with access denied
		$s3_client = $this->create_mock_s3_client([
			'error_code' => 'AccessDenied',
			'error_message' => 'Access Denied',
			'should_succeed' => false
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		$test_files = [];

		// Create temporary test files.
		foreach ( $test_data['files'] as $file ) {
			// Create the test file
			$test_file_path = $this->create_temp_file($file['name'], 'Test content');
			$test_files[$file['name']] = $test_file_path;

			// Simulate WordPress upload
			$upload = $this->create_test_upload($test_file_path, $file['type']);

			// Set up error logging capture
			$error_log_file = tempnam(sys_get_temp_dir(), 'phpunit_error_log');
			$old_error_log = ini_get('error_log');
			ini_set('error_log', $error_log_file);
			
			// Run the test - this should log the error but still return the upload data
			$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
			
			// Restore error logging
			ini_set('error_log', $old_error_log);
			
			// Verify the result is still the original upload data
			Assert::assertSame($upload, $result, 'Upload data should be returned unchanged for ' . $file['name'] . ' even on error');
			
			// Check the error log - we expect either "Access Denied" or "Failed to upload" or "S3 upload error"
			$log_content = file_get_contents($error_log_file);
			$expected_messages = [
				'Access Denied',
				'Failed to upload',
				'S3 upload error',
				'[AccessDenied]'
			];
			
			$message_found = false;
			foreach ($expected_messages as $message) {
				if (strpos($log_content, $message) !== false) {
					$message_found = true;
					break;
				}
			}
			
			Assert::assertTrue($message_found, 'Error for ' . $file['name'] . ' should be logged');
			
			// Clean up
			unlink($error_log_file);
		}

		// Clean up test files
		foreach ($test_files as $file_path) {
			unlink($file_path);
		}
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
