<?php
/**
 * Integration tests for S3 Media Sync image editor functionality
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync;
use S3_Media_Sync_Settings;
use S3_Media_Sync\Tests\TestCase;
use WP_Image_Editor;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\File_Comparison;

/**
 * Test case for S3 Media Sync image editor functionality.
 *
 * @group integration
 * @group image-editor
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Settings
 */
class ImageEditorTest extends TestCase {
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
	 * Test data for image editor scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_image_edits(): array {
		$upload_dir = wp_upload_dir();
		$year_month = date( 'Y/m' );
		$base_path = str_replace( 'vip://', '', $upload_dir['path'] );
		
		return [
			'image crop' => [
				[
					'operation' => 'crop',
					'filename' => 'test-image-cropped.jpg',
					'mime_type' => 'image/jpeg',
					'expected_s3_path' => trailingslashit( $base_path ) . 'test-image-cropped.jpg',
					'params' => [
						'x' => 0,
						'y' => 0,
						'width' => 50,
						'height' => 50,
					],
				],
			],
			'image resize' => [
				[
					'operation' => 'resize',
					'filename' => 'test-image-resized.jpg',
					'mime_type' => 'image/jpeg',
					'expected_s3_path' => trailingslashit( $base_path ) . 'test-image-resized.jpg',
					'params' => [
						'width' => 100,
						'height' => 100,
					],
				],
			],
			'image rotate' => [
				[
					'operation' => 'rotate',
					'filename' => 'test-image-rotated.jpg',
					'mime_type' => 'image/jpeg',
					'expected_s3_path' => trailingslashit( $base_path ) . 'test-image-rotated.jpg',
					'params' => [
						'angle' => 90,
					],
				],
			],
		];
	}

	/**
	 * Test data for image editor error handling scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_image_editor_operations(): array {
		$upload_dir = wp_upload_dir();
		
		return [
			'image processing' => [
				[
					'filename' => 'test-image-error.jpg',
					'mime_type' => 'image/jpeg',
					'expected_error' => 'Failed to upload',
				],
			],
			'file validation' => [
				[
					'filename' => 'test-image-validation.jpg',
					'mime_type' => 'image/jpeg',
					'expected_error' => 'Failed to upload',
				],
			],
		];
	}

	/**
	 * Test that image editor changes sync to S3
	 *
	 * @dataProvider data_provider_image_edits
	 */
	public function test_image_editor_changes_sync_to_s3(array $test_data): void {
		$operation = $test_data['operation'];
		$params = array_values($test_data['params']);

		// Create a mock S3 client that will track uploaded keys
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

		// Create a test image
		$local_file = $this->create_temp_file('test-image.jpg', $this->create_test_image());
		$file_path = $local_file->get_path();

		// Create the S3 file object
		$s3_file = $this->create_test_s3_file($local_file, $this->bucket);
		$s3_path = 's3://' . $this->bucket->get_name() . '/' . $s3_file->get_key();

		// Simulate WordPress upload
		$upload = $this->create_test_upload($local_file, 'image/jpeg');

		// Upload to S3
		$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');

		// Perform image operation
		$editor = wp_get_image_editor($file_path);
		if (is_wp_error($editor)) {
			Assert::fail('Failed to create image editor: ' . $editor->get_error_message());
		}

		$editor->$operation(...$params);
		$editor->save();

		// Verify both original and edited files exist in S3
		$s3_exists = file_exists($s3_path);
		Assert::assertTrue($s3_exists, 'Original file should exist in S3');

		// Clean up
		$local_path = $local_file->get_path();
		if (file_exists($local_path)) {
			unlink($local_path);
		}
	}

	/**
	 * Test error handling during image operations
	 *
	 * @dataProvider data_provider_image_editor_operations
	 */
	public function test_image_editor_error_handling(array $test_data): void {
		$error_code = $test_data['expected_error'];
		$error_message = $test_data['expected_error'];

		// Create a mock S3 client that will fail
		$s3_client = $this->create_mock_s3_client([
			'error_code' => $error_code,
			'error_message' => $error_message,
			'should_succeed' => false
		]);

		// Set up error logging capture
		$error_log_file = tempnam(sys_get_temp_dir(), 'phpunit_error_log');
		$old_error_log = ini_get('error_log');
		ini_set('error_log', $error_log_file);

		// Create a test image
		$local_file = $this->create_temp_file('test-image.jpg', $this->create_test_image());

		// Simulate WordPress upload
		$upload = $this->create_test_upload($local_file, 'image/jpeg');

		// Try to upload to S3
		$result = $this->s3_media_sync->add_attachment_to_s3($upload, 'upload');

		// Verify error is logged
		$log_content = file_get_contents($error_log_file);
		Assert::assertStringContainsString($error_message, $log_content, 'Error should be logged');

		// Clean up
		unlink($error_log_file);
		$local_path = $local_file->get_path();
		if (file_exists($local_path)) {
			unlink($local_path);
		}
		ini_set('error_log', $old_error_log);
	}

	/**
	 * Test image editor integration with S3
	 */
	public function test_image_editor_integration(): void {
		// Create a mock S3 client that will handle stream operations
		$s3_client = $this->create_mock_s3_client([
			'handle_streams' => true
		]);

		// Set up the plugin
		$this->s3_media_sync->setup();

		// Create test image file
		$local_file = $this->create_temp_file('test-image.jpg', $this->create_test_image());
		$s3_file = $this->create_test_s3_file($local_file, $this->bucket);
		$this->comparison = File_Comparison::compare($local_file, $s3_file);

		// Get WordPress image editor
		$editor = wp_get_image_editor($local_file->get_path());
		if (is_wp_error($editor)) {
			Assert::fail('Failed to create image editor: ' . $editor->get_error_message());
		}

		// Resize the image
		$editor->resize(100, 100, true);
		
		// Save the edited image
		$saved = $editor->save();
		if (is_wp_error($saved)) {
			Assert::fail('Failed to save edited image: ' . $saved->get_error_message());
		}

		// Verify the file exists locally
		Assert::assertTrue(file_exists($saved['path']), 'Edited image should exist locally');

		// Verify the file exists on S3
		$s3_path = 's3://' . $this->bucket->get_name() . '/' . $s3_file->get_key();
		Assert::assertTrue(file_exists($s3_path), 'Edited image should exist on S3');

		// Clean up
		$local_path = $local_file->get_path();
		if (file_exists($local_path)) {
			unlink($local_path);
		}
		if (file_exists($saved['path'])) {
			unlink($saved['path']);
		}
	}

	/**
	 * Creates a test image
	 */
	protected function create_test_image(): string {
		$image = imagecreatetruecolor(200, 200);
		imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
		ob_start();
		imagejpeg($image);
		$contents = ob_get_clean();
		imagedestroy($image);
		return $contents;
	}

	public function tear_down(): void {
		parent::tear_down();
		if (is_string($this->test_file) && file_exists($this->test_file)) {
			unlink($this->test_file);
		} elseif ($this->test_file instanceof Local_File && file_exists($this->test_file->get_path())) {
			unlink($this->test_file->get_path());
		}
		Mockery::close();
	}
} 
