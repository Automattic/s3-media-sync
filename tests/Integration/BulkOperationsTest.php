<?php
/**
 * Integration tests for S3 Media Sync bulk operations
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
 * Test case for S3 Media Sync bulk operations functionality.
 *
 * @group integration
 * @group bulk-operations
 * @covers S3_Media_Sync
 * @uses S3_Media_Sync_Stream_Wrapper
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
		// Set up the plugin with mock client.
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->default_settings
		);

		$s3_client = $this->create_mock_s3_client();
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

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

			// Verify the file exists in S3
			$s3_path = 's3://' . $this->default_settings['bucket'] . '/wp-content/uploads' . str_replace(wp_get_upload_dir()['baseurl'], '', $upload['url']);
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
		// Set up the plugin with mock client that will fail uploads.
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->default_settings
		);

		$s3_client = $this->create_mock_s3_client([
			'error_code' => 'AccessDenied',
			'error_message' => 'Access Denied',
			'should_succeed' => false
		]);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

		$this->s3_media_sync->setup();

		$test_files = [];

		// Process each file and verify error handling
		foreach ( $test_data['files'] as $file ) {
			// Create the test file
			$test_file_path = $this->create_temp_file($file['name']);
			$test_files[] = $test_file_path;

			// Simulate WordPress upload
			$upload = $this->create_test_upload($test_file_path, $file['type']);

			// Test the upload sync should fail
			$error_triggered = false;
			set_error_handler(function($errno, $errstr) use (&$error_triggered) {
				$error_triggered = true;
				Assert::assertStringContainsString('Failed to open stream', $errstr);
				Assert::assertStringContainsString('Access Denied', $errstr);
				return true;
			});

			try {
				$this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
			} catch (\Exception $e) {
				// Expected
			}

			restore_error_handler();
			Assert::assertTrue($error_triggered, 'Expected PHP error was not triggered for ' . $file['name']);
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
