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

/**
 * Test case for S3 Media Sync media upload functionality.
 *
 * @group integration
 * @group media-upload
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
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
		// Set up the plugin with mock client.
		$this->settings_handler->update_settings($this->default_settings);
		$this->create_mock_s3_client();
		$this->s3_media_sync->setup();

		// Create a temporary test file with specific content.
		$test_content = 'Test file content for ' . $test_data['file']['name'];
		$test_file_path = $this->create_temp_file($test_data['file']['name'], $test_content);

		// Simulate WordPress upload.
		$upload = $this->create_test_upload($test_file_path, $test_data['file']['type']);

		// Test the upload sync.
		$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');

		// Verify the upload was processed.
		Assert::assertSame($upload, $result, 'Upload data should be returned unchanged');

		// Verify the file exists in S3.
		$s3_path = 's3://' . $this->default_settings['bucket'] . '/' . $test_data['expected_s3_path'];
		Assert::assertTrue(file_exists($s3_path), 'File should exist in S3 after upload');

		// Verify the content was uploaded correctly.
		$s3_content = file_get_contents($s3_path);
		Assert::assertSame($test_content, $s3_content, 'S3 file content should match original');

		// Clean up local file.
		unlink($test_file_path);
	}

	/**
	 * Test media upload error handling.
	 *
	 * @dataProvider data_provider_media_uploads
	 */
	public function test_media_upload_error_handling(array $test_data): void {
		// Set up the plugin with mock client that will fail uploads.
		$this->settings_handler->update_settings($this->default_settings);
		$this->create_mock_s3_client([
			'error_code' => 'AccessDenied',
			'error_message' => 'Access Denied',
			'should_succeed' => false
		]);
		$this->s3_media_sync->setup();

		// Create a temporary test file.
		$test_file_path = $this->create_temp_file($test_data['file']['name']);

		// Simulate WordPress upload.
		$upload = $this->create_test_upload($test_file_path, $test_data['file']['type']);

		// Test the upload sync should fail.
		$exception_thrown = false;
		try {
			$this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
		} catch (\Aws\S3\Exception\S3Exception $e) {
			$exception_thrown = true;
			Assert::assertSame('AccessDenied', $e->getAwsErrorCode());
			Assert::assertStringContainsString('Access Denied', $e->getMessage());
		}

		Assert::assertTrue($exception_thrown, 'Expected S3Exception was not thrown');

		// Clean up local file.
		unlink($test_file_path);
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
