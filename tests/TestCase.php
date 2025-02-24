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
use S3_Media_Sync_Client_Factory;
use S3_Media_Sync_Stream_Wrapper;
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
	 * @var \S3_Media_Sync_Settings $settings_handler The settings handler.
	 */
	protected $settings_handler;

	/**
	 * Creates a mock S3 client for testing.
	 *
	 * @param array $options Options for configuring the mock client:
	 *                      - error_code: AWS error code to simulate
	 *                      - error_message: Error message to include
	 *                      - should_succeed: Whether operations should succeed
	 * @return S3Client|Mockery\MockInterface
	 */
	protected function create_mock_s3_client(array $options = []): Mockery\MockInterface {
		$options = array_merge([
			'error_code' => null,
			'error_message' => null,
			'should_succeed' => true,
		], $options);

		// Track uploaded content
		$uploaded_content = [];

		// Create a mock client factory
		$mock_factory = Mockery::mock(S3_Media_Sync_Client_Factory::class);
		
		// Create the mock S3 client
		$mock_client = Mockery::mock(S3Client::class);

		// Configure the mock client based on options
		if (!$options['should_succeed'] && $options['error_code']) {
			$mock_client->shouldReceive('doesBucketExist')
				->andReturnUsing(function() use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						new Command('HeadBucket'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('getCommand')
				->andReturnUsing(function($command, $args) use ($options) {
					$cmd = new Command($command, $args);
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						$cmd,
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('execute')
				->andReturnUsing(function($command) use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						$command,
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('deleteMatchingObjects')
				->andReturnUsing(function($bucket, $prefix) use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						new Command('DeleteObjects'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('putObject')
				->andReturnUsing(function($args) use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						new Command('PutObject'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('getObject')
				->andReturnUsing(function($args) use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						new Command('GetObject'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});

			$mock_client->shouldReceive('headObject')
				->andReturnUsing(function($args) use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						sprintf('[%s] %s', $options['error_code'], $options['error_message']),
						new Command('HeadObject'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});
		} else {
			// Configure successful operations
			$mock_client->shouldReceive('doesBucketExist')
				->andReturn(true);

			$mock_client->shouldReceive('getCommand')
				->andReturnUsing(function($command, $args) {
					return new Command($command, $args);
				});

			$mock_client->shouldReceive('execute')
				->andReturnUsing(function($command) use (&$uploaded_content) {
					$name = $command->getName();
					$args = $command->toArray();

					switch ($name) {
						case 'PutObject':
							$key = $args['Key'];
							$uploaded_content[$key] = (string)$args['Body'];
							return new Result([]);
						case 'GetObject':
							$key = $args['Key'];
							$content = $uploaded_content[$key] ?? 'Test content';
							return new Result(['Body' => Utils::streamFor($content)]);
						case 'HeadObject':
							$key = $args['Key'];
							if (!isset($uploaded_content[$key])) {
								throw new \Aws\S3\Exception\S3Exception(
									'Not Found',
									$command,
									['code' => 'NoSuchKey']
								);
							}
							return new Result(['ContentLength' => strlen($uploaded_content[$key])]);
						case 'DeleteObject':
							$key = $args['Key'];
							unset($uploaded_content[$key]);
							return new Result([]);
						default:
							return new Result([]);
					}
				});

			$mock_client->shouldReceive('putObject')
				->andReturnUsing(function($args) use (&$uploaded_content) {
					$key = $args['Key'];
					$uploaded_content[$key] = (string)$args['Body'];
					return new Result([]);
				});

			$mock_client->shouldReceive('getObject')
				->andReturnUsing(function($args) use (&$uploaded_content) {
					$key = $args['Key'];
					$content = $uploaded_content[$key] ?? 'Test content';
					return new Result(['Body' => Utils::streamFor($content)]);
				});

			$mock_client->shouldReceive('headObject')
				->andReturnUsing(function($args) use (&$uploaded_content) {
					$key = $args['Key'];
					if (!isset($uploaded_content[$key])) {
						throw new \Aws\S3\Exception\S3Exception(
							'Not Found',
							new Command('HeadObject'),
							['code' => 'NoSuchKey']
						);
					}
					return new Result(['ContentLength' => strlen($uploaded_content[$key])]);
				});

			$mock_client->shouldReceive('deleteObject')
				->andReturnUsing(function($args) use (&$uploaded_content) {
					$key = $args['Key'];
					unset($uploaded_content[$key]);
					return new Result([]);
				});

			$mock_client->shouldReceive('deleteMatchingObjects')
				->andReturnUsing(function($bucket, $prefix) use (&$uploaded_content) {
					foreach ($uploaded_content as $key => $content) {
						if (strpos($key, $prefix) === 0) {
							unset($uploaded_content[$key]);
						}
					}
					return new Result([]);
				});
		}

		// Configure the factory to return our mock client
		$mock_factory->shouldReceive('create')
			->andReturn($mock_client);

		$mock_factory->shouldReceive('configure_stream_wrapper')
			->with($mock_client, Mockery::any())
			->andReturnUsing(function($client, $settings) {
				S3_Media_Sync_Stream_Wrapper::register($client);
				stream_context_set_option(stream_context_get_default(), 's3', 'ACL', $settings['object_acl'] ?? 'public-read');
				stream_context_set_option(stream_context_get_default(), 's3', 'seekable', true);
			});

		// Replace the real factory with our mock in the global scope
		$GLOBALS['s3_media_sync_client_factory'] = $mock_factory;

		return $mock_client;
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

		$this->settings_handler = new S3_Media_Sync_Settings();
		$this->settings_handler->update_settings($this->default_settings);
		$this->s3_media_sync = new S3_Media_Sync($this->settings_handler);

		// Create a default mock client
		$this->create_mock_s3_client();
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		delete_option('s3_media_sync_settings');
		unset($GLOBALS['s3_media_sync_client_factory']);
		Mockery::close();
	}
} 
