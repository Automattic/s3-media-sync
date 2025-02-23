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
 * @covers S3_Media_Sync
 * @uses S3_Media_Sync_Stream_Wrapper
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
		// Set up the plugin with mock client
		$settings = $this->default_settings;
		$settings['bucket'] = $test_data['bucket'];

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$s3_client = $this->create_mock_s3_client();
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

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
		// Set up the plugin with mock client that will fail deletions
		$settings = $this->default_settings;
		$settings['bucket'] = $test_data['bucket'];

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
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

		// Test the deletion should fail with an S3Exception
		$exception_thrown = false;
		try {
			$this->s3_media_sync->delete_attachment_from_s3($post_id);
		} catch (\Aws\S3\Exception\S3Exception $e) {
			$exception_thrown = true;
			Assert::assertSame('AccessDenied', $e->getAwsErrorCode());
			Assert::assertStringContainsString('Access Denied', $e->getMessage());
		}

		Assert::assertTrue($exception_thrown, 'Expected S3Exception was not thrown');

		// Clean up
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
