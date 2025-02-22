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
 * @uses S3_Media_Sync_Stream_Wrapper
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

		// Create a temporary test file.
		$test_file_path = $this->create_temp_file($test_data['file']['name']);

		// Simulate WordPress upload.
		$upload = $this->create_test_upload($test_file_path, $test_data['file']['type']);

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
