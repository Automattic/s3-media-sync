<?php
/**
 * Integration tests for S3 Media Sync error handling
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Aws\Command;
use Aws\CommandInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync;
use Mockery;
use PHPUnit\Framework\Assert;

/**
 * Test case for S3 Media Sync error handling functionality.
 *
 * @group integration
 * @group error-handling
 * @covers S3_Media_Sync
 * @uses S3_Media_Sync_Stream_Wrapper
 */
class ErrorHandlingTest extends TestCase {

	protected S3_Media_Sync $s3_media_sync;
	protected $test_file;
	protected $settings;

	public function set_up(): void {
		parent::set_up();
		
		$this->s3_media_sync = new S3_Media_Sync();
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->default_settings
		);
		
		$this->test_file = $this->create_temp_file();
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
	 * @dataProvider data_provider_error_scenarios
	 */
	public function test_error_handling_during_operations($error_code, $error_message) {
		$s3_client = $this->create_mock_s3_client([
			'error_code' => $error_code,
			'error_message' => $error_message,
			'should_succeed' => false
		]);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

		// Register the stream wrapper
		$this->s3_media_sync->register_stream_wrapper();

		$upload = $this->create_test_upload($this->test_file);
		
		$exception_thrown = false;
		try {
			$this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
		} catch (\Aws\S3\Exception\S3Exception $e) {
			$exception_thrown = true;
			Assert::assertSame($error_code, $e->getAwsErrorCode());
			Assert::assertStringContainsString($error_message, $e->getMessage());
		}
		
		Assert::assertTrue($exception_thrown, 'Expected S3Exception was not thrown');
	}

	/**
	 * @dataProvider data_provider_error_scenarios
	 */
	public function test_stream_wrapper_error_handling($error_code, $error_message) {
		$s3_client = $this->create_mock_s3_client([
			'error_code' => $error_code,
			'error_message' => $error_message,
			'should_succeed' => false
		]);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

		// Register the stream wrapper
		$this->s3_media_sync->register_stream_wrapper();

		$s3_path = 's3://' . $this->default_settings['bucket'] . '/test.txt';
		
		$error_triggered = false;
		set_error_handler(function($errno, $errstr) use (&$error_triggered, $error_message) {
			$error_triggered = true;
			Assert::assertStringContainsString('Failed to open stream', $errstr);
			Assert::assertStringContainsString($error_message, $errstr);
			return true;
		});

		try {
			file_get_contents($s3_path);
		} catch (\Exception $e) {
			// Expected
		}

		restore_error_handler();
		Assert::assertTrue($error_triggered, 'Expected PHP error was not triggered');
	}

	public function tear_down(): void {
		parent::tear_down();
		if ($this->test_file && file_exists($this->test_file)) {
			unlink($this->test_file);
		}
		Mockery::close();
	}
}
