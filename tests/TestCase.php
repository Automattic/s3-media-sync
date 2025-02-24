<?php
/**
 * Base test case for all S3 Media Sync tests
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests;

use Aws\Command;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Mockery;
use S3_Media_Sync;
use S3_Media_Sync_Settings;
use Yoast\WPTestUtils\WPIntegration\TestCase as WPTestCase;

/**
 * Base test case for S3 Media Sync.
 */
abstract class TestCase extends WPTestCase {
	use Tests_Reflection;

	/**
	 * Holds a reference to the S3_Media_Sync instance used for testing.
	 *
	 * @var \S3_Media_Sync $s3_media_sync The S3_Media_Sync instance.
	 */
	protected S3_Media_Sync $s3_media_sync;

	/**
	 * Default test settings.
	 *
	 * @var array
	 */
	protected array $default_settings;

	/**
	 * Settings handler for the S3_Media_Sync instance.
	 *
	 * @var \S3_Media_Sync_Settings_Handler $settings_handler The settings handler.
	 */
	protected $settings_handler;

	/**
	 * Creates a mock S3 client for testing.
	 *
	 * @param array $options Options for configuring the mock client:
	 *                      - error_code: AWS error code to simulate
	 *                      - error_message: Error message to include
	 *                      - should_succeed: Whether operations should succeed
	 * @return S3Client
	 */
	protected function create_mock_s3_client(array $options = []): S3Client {
		$options = array_merge([
			'error_code' => null,
			'error_message' => null,
			'should_succeed' => true,
		], $options);

		// Track uploaded content
		$uploaded_content = [];

		// Create a mock handler
		$handler = function ($command, $request) use ($options, &$uploaded_content) {
			if (!$options['should_succeed'] && $options['error_code']) {
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
						$options['error_code'],
						$options['error_message']
					)
				);

				$exception = new \Aws\S3\Exception\S3Exception(
					sprintf('[%s] %s', $options['error_code'], $options['error_message']),
					$command,
					[
						'response' => $response,
						'code' => $options['error_code'],
						'message' => $options['error_message'],
						'type' => 'client',
						'request_id' => '1234567890ABCDEF',
						'aws' => [
							'code' => $options['error_code'],
							'type' => 'client',
							'message' => $options['error_message'],
							'request_id' => '1234567890ABCDEF'
						]
					]
				);

				return Promise\Create::rejectionFor($exception);
			}

			// Handle different command types
			$command_name = $command->getName();
			$key = isset($command['Key']) ? $command['Key'] : null;

			switch ($command_name) {
				case 'PutObject':
					$uploaded_content[$key] = (string)$command['Body'];
					return Promise\Create::promiseFor(new Result([]));

				case 'GetObject':
					if (!isset($uploaded_content[$key])) {
						return Promise\Create::promiseFor(new Result(['Body' => Utils::streamFor('Test content')]));
					}
					return Promise\Create::promiseFor(new Result(['Body' => Utils::streamFor($uploaded_content[$key])]));

				case 'HeadObject':
					if (!isset($uploaded_content[$key])) {
						return Promise\Create::rejectionFor(
							new \Aws\S3\Exception\S3Exception(
								'Not Found',
								$command,
								['code' => 'NoSuchKey']
							)
						);
					}
					return Promise\Create::promiseFor(new Result(['ContentLength' => strlen($uploaded_content[$key])]));

				case 'DeleteObject':
					unset($uploaded_content[$key]);
					return Promise\Create::promiseFor(new Result([]));

				default:
					return Promise\Create::promiseFor(new Result([]));
			}
		};

		// Create the S3 client with the mock handler
		return new S3Client([
			'version' => 'latest',
			'region' => $this->default_settings['region'],
			'credentials' => [
				'key' => $this->default_settings['key'],
				'secret' => $this->default_settings['secret']
			],
			'handler' => $handler,
			'use_aws_shared_config_files' => false,
			'endpoint' => 'http://localhost',
			'validate' => false
		]);
	}

	/**
	 * Creates a mock command that supports array access.
	 *
	 * @param array $data Initial command data.
	 * @return \Mockery\MockInterface
	 */
	protected function create_mock_command(array $data = []): \Mockery\MockInterface {
		$command = Mockery::mock(CommandInterface::class);
		$command_data = $data;
		
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
	 * Creates a temporary test file with optional content.
	 *
	 * @param string $filename Optional filename. If not provided, a random name will be used.
	 * @param string $content Optional content for the file.
	 * @return string The path to the created file.
	 */
	protected function create_temp_file(?string $filename = null, string $content = 'Test content'): string {
		if ($filename === null) {
			$file_path = wp_tempnam();
		} else {
			$upload_dir = wp_upload_dir();
			$file_path = $upload_dir['path'] . '/' . $filename;
		}

		file_put_contents($file_path, $content);
		return $file_path;
	}

	/**
	 * Creates a test upload array for simulating WordPress uploads.
	 *
	 * @param string $file_path The path to the file.
	 * @param string $mime_type The mime type of the file.
	 * @return array The upload array.
	 */
	protected function create_test_upload(string $file_path, string $mime_type = 'image/jpeg'): array {
		$upload_dir = wp_upload_dir();
		return [
			'file' => $file_path,
			'url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path),
			'type' => $mime_type,
			'path' => $file_path,
			'subdir' => '',
			'basedir' => dirname($file_path),
			'baseurl' => $upload_dir['baseurl']
		];
	}

	/**
	 * Prepares the test environment before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		// Ensure the AWS SDK can be loaded.
		if ( ! class_exists( '\\Aws\\S3\\S3Client' ) ) {
			// Require AWS Autoloader file.
			require_once dirname( __FILE__, 2 ) . '/vendor/autoload.php';
		}

		$this->default_settings = [
			'bucket' => 'test-bucket',
			'key' => 'test-key',
			'secret' => 'test-secret',
			'region' => 'test-region',
			'object_acl' => 'public-read',
		];

		$this->s3_media_sync = S3_Media_Sync::init();
		$this->settings_handler = $this->s3_media_sync->get_settings_handler();
		
		// Initialize settings
		update_option('s3_media_sync_settings', $this->default_settings);
		$this->settings_handler->update_settings($this->default_settings);
	}

	/**
	 * Nullify the S3_Media_Sync instance after each test.
	 *
	 * @throws \ReflectionException If reflection fails.
	 */
	public function tear_down(): void {
		parent::tear_down();

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'instance',
			null
		);

		delete_option('s3_media_sync_settings');

		Mockery::close();
	}
} 
