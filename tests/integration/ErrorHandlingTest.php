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
		$this->settings = [
			'bucket' => 'test-bucket',
			'key' => 'test-key',
			'secret' => 'test-secret',
			'region' => 'test-region',
			'object_acl' => 'public-read'
		];
		
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->settings
		);
		
		$this->test_file = wp_tempnam();
		if ($this->test_file === false) {
			Assert::fail('Failed to create temporary file');
		}
		file_put_contents($this->test_file, 'test content');
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
	 * Creates a mock command that supports array access.
	 *
	 * @param string $error_code The AWS error code.
	 * @param string $error_message The error message.
	 * @return \Mockery\MockInterface
	 */
	private function create_mock_command($error_code, $error_message) {
		$command = Mockery::mock(CommandInterface::class);
		$command_data = [];
		
		$command->shouldReceive('offsetGet')
			->with(Mockery::any())
			->andReturnUsing(function($key) use (&$command_data) {
				return $command_data[$key] ?? null;
			});
		
		$command->shouldReceive('offsetSet')
			->with(Mockery::any(), Mockery::any())
			->andReturnUsing(function($key, $value) use (&$command_data) {
				$command_data[$key] = $value;
			});
		
		$command->shouldReceive('offsetExists')
			->with(Mockery::any())
			->andReturnUsing(function($key) use (&$command_data) {
				return isset($command_data[$key]);
			});
		
		$command->shouldReceive('offsetUnset')
			->with(Mockery::any())
			->andReturnUsing(function($key) use (&$command_data) {
				unset($command_data[$key]);
			});

		return $command;
	}

	/**
	 * Creates a mock S3 client with error handling.
	 *
	 * @param string $error_code The AWS error code.
	 * @param string $error_message The error message.
	 * @return S3Client
	 */
	private function create_mock_s3_client($error_code, $error_message) {
		$command = $this->create_mock_command($error_code, $error_message);

		// Create a mock handler that always throws the specified exception
		$handler = function ($command, $request) use ($error_code, $error_message) {
			$response = new Response(
				400,
				[
					'X-Amz-Request-Id' => '1234567890ABCDEF',
					'Content-Type' => 'application/xml'
				],
				sprintf(
					'<?xml version="1.0" encoding="UTF-8"?>
					<Error>
						<Code>%s</Code>
						<Message>%s</Message>
						<RequestId>1234567890ABCDEF</RequestId>
						<HostId>host-id</HostId>
					</Error>',
					$error_code,
					$error_message
				)
			);

			$exception = new S3Exception(
				sprintf('[%s] %s', $error_code, $error_message),
				$command,
				[
					'response' => $response,
					'code' => $error_code,
					'message' => $error_message,
					'type' => 'client',
					'request_id' => '1234567890ABCDEF',
					'aws' => [
						'code' => $error_code,
						'type' => 'client',
						'message' => $error_message,
						'request_id' => '1234567890ABCDEF'
					]
				]
			);

			return Promise\Create::rejectionFor($exception);
		};

		// Create the S3 client with the mock handler
		$s3_client = new S3Client([
			'version' => 'latest',
			'region' => $this->settings['region'],
			'credentials' => [
				'key' => $this->settings['key'],
				'secret' => $this->settings['secret']
			],
			'handler' => $handler,
			'use_aws_shared_config_files' => false,
			'endpoint' => 'http://localhost',
			'validate' => false
		]);

		return $s3_client;
	}

	/**
	 * @dataProvider data_provider_error_scenarios
	 */
	public function test_error_handling_during_operations($error_code, $error_message) {
		$s3_client = $this->create_mock_s3_client($error_code, $error_message);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

		// Register the stream wrapper
		$this->s3_media_sync->register_stream_wrapper();

		$upload = [
			'file' => $this->test_file,
			'url' => 'http://example.com/test.jpg',
			'type' => 'image/jpeg',
			'path' => $this->test_file,
			'subdir' => '',
			'basedir' => dirname($this->test_file),
			'baseurl' => 'http://example.com'
		];
		
		$exception_thrown = false;
		try {
			$this->s3_media_sync->add_attachment_to_s3($upload, 'upload');
		} catch (S3Exception $e) {
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
		$s3_client = $this->create_mock_s3_client($error_code, $error_message);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$s3_client
		);

		// Register the stream wrapper
		$this->s3_media_sync->register_stream_wrapper();

		$s3_path = 's3://' . $this->settings['bucket'] . '/test.txt';
		
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
