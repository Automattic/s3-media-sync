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
use S3_Media_Sync\Client\S3_Media_Sync_Client_Factory;
use S3_Media_Sync_Stream_Wrapper;
use Yoast\WPTestUtils\WPIntegration\TestCase as WPTestCase;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;

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
	 *                      - handle_streams: Whether to handle stream operations
	 *                      - debug_callback: Callback function for debugging
	 * @return S3Client|Mockery\MockInterface
	 */
	protected function create_mock_s3_client(array $options = []): Mockery\MockInterface {
		$options = array_merge([
			'error_code' => null,
			'error_message' => null,
			'should_succeed' => true,
			'handle_streams' => false,
			'debug_callback' => null,
		], $options);

		// Track uploaded content
		$uploaded_content = [];

		// Create a mock client factory
		$mock_factory = Mockery::mock(S3_Media_Sync_Client_Factory::class);
		
		// Create the mock S3 client
		$mock_client = Mockery::mock(S3Client::class);

		// Configure the factory to return our mock client
		$mock_factory->shouldReceive('create')
			->andReturn($mock_client);

		if ($options['handle_streams']) {
			// Mock stream wrapper operations
			$mock_client->shouldReceive('doesBucketExist')
				->andReturn(true);

			$mock_client->shouldReceive('headBucket')
				->andReturn(new Result([]));

			$mock_client->shouldReceive('getCommand')
				->andReturnUsing(function($command, $args) {
					return new Command($command, $args);
				});

			$mock_client->shouldReceive('execute')
				->andReturnUsing(function($command) use (&$uploaded_content, $options) {
					$name = $command->getName();
					$args = $command->toArray();

					if ($options['debug_callback']) {
						$options['debug_callback']($name, $args);
					}

					switch ($name) {
						case 'PutObject':
							$key = $args['Key'];
							$content = null;
							
							if (isset($args['SourceFile'])) {
								$content = file_get_contents($args['SourceFile']);
							} else if (isset($args['Body'])) {
								$content = (string)$args['Body'];
							} else {
								throw new \RuntimeException("No content source (SourceFile or Body) provided for upload");
							}
							
							if ($content !== null) {
								$uploaded_content[$key] = $content;
							}
							return new Result([]);

						case 'HeadObject':
							$key = $args['Key'];
							if (isset($uploaded_content[$key])) {
								return new Result(['ContentLength' => strlen($uploaded_content[$key])]);
							}
							throw new \Aws\S3\Exception\S3Exception(
								'Not Found',
								$command,
								['code' => 'NoSuchKey']
							);

						case 'GetObject':
							$key = $args['Key'];
							if (isset($uploaded_content[$key])) {
								return new Result([
									'Body' => Utils::streamFor($uploaded_content[$key])
								]);
							}
							throw new \Aws\S3\Exception\S3Exception(
								'Not Found',
								$command,
								['code' => 'NoSuchKey']
							);

						default:
							return new Result([]);
					}
				});

			$mock_client->shouldReceive('putObject')
				->andReturnUsing(function($args) use (&$uploaded_content, $options) {
					if ($options['debug_callback']) {
						$options['debug_callback']('putObject', $args);
					}
					$key = $args['Key'];
					$content = null;
					
					if (isset($args['SourceFile'])) {
						$content = file_get_contents($args['SourceFile']);
					} else if (isset($args['Body'])) {
						$content = (string)$args['Body'];
					} else {
						throw new \RuntimeException("No content source (SourceFile or Body) provided for upload");
					}
					
					if ($content !== null) {
						$uploaded_content[$key] = $content;
					}
					
					return new Result([]);
				});

			$mock_client->shouldReceive('headObject')
				->andReturnUsing(function($args) use (&$uploaded_content, $options) {
					if ($options['debug_callback']) {
						$options['debug_callback']('headObject', $args);
					}
					$key = $args['Key'];
					
					if (isset($uploaded_content[$key])) {
						return new Result(['ContentLength' => strlen($uploaded_content[$key])]);
					}
					throw new \Aws\S3\Exception\S3Exception(
						'Not Found',
						new Command('HeadObject'),
						['code' => 'NoSuchKey']
					);
				});

			$mock_client->shouldReceive('getObject')
				->andReturnUsing(function($args) use (&$uploaded_content, $options) {
					if ($options['debug_callback']) {
						$options['debug_callback']('getObject', $args);
					}
					$key = $args['Key'];
					if (isset($uploaded_content[$key])) {
						return new Result([
							'Body' => Utils::streamFor($uploaded_content[$key])
						]);
					}
					throw new \Aws\S3\Exception\S3Exception(
						'Not Found',
						new Command('GetObject'),
						['code' => 'NoSuchKey']
					);
				});

			// Configure the stream wrapper
			$mock_client->shouldReceive('registerStreamWrapper')
				->andReturnUsing(function() use ($mock_client) {
					S3_Media_Sync_Stream_Wrapper::register($mock_client);
					return true;
				});
		} elseif (!$options['should_succeed'] && $options['error_code']) {
			// Always return the mock client first, even for credential errors
			$mock_client->shouldReceive('doesBucketExist')
				->andReturnUsing(function() use ($options) {
					throw new \RuntimeException(
						"[{$options['error_code']}] {$options['error_message']}"
					);
				});
			
			// Add mock for headBucket method
			$mock_client->shouldReceive('headBucket')
				->andReturnUsing(function() use ($options) {
					throw new \Aws\S3\Exception\S3Exception(
						"[{$options['error_code']}] {$options['error_message']}",
						new Command('HeadBucket'),
						[
							'code' => $options['error_code'],
							'message' => $options['error_message']
						]
					);
				});
				
				$mock_client->shouldReceive('getCommand')
					->andReturnUsing(function($command, $args) use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							new Command($command, $args),
							[
								'code' => $options['error_code'],
								'message' => $options['error_message']
							]
						);
					});
					
				$mock_client->shouldReceive('execute')
					->andReturnUsing(function($command) use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							$command,
							[
								'code' => $options['error_code'],
								'message' => $options['error_message']
							]
						);
					});
					
				$mock_client->shouldReceive('putObject')
					->andReturnUsing(function() use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							new Command('PutObject'),
							[
								'code' => $options['error_code'],
								'message' => $options['error_message']
							]
						);
					});
					
				$mock_client->shouldReceive('getObject')
					->andReturnUsing(function() use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							new Command('GetObject'),
							[
								'code' => $options['error_code'],
								'message' => $options['error_message']
							]
						);
					});
					
				$mock_client->shouldReceive('headObject')
					->andReturnUsing(function() use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							new Command('HeadObject'),
							[
								'code' => $options['error_code'],
								'message' => $options['error_message']
							]
						);
					});
					
				$mock_client->shouldReceive('deleteMatchingObjects')
					->andReturnUsing(function() use ($options) {
						throw new \Aws\S3\Exception\S3Exception(
							"[{$options['error_code']}] {$options['error_message']}",
							new Command('DeleteObject'),
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
				
			// Add mock for headBucket method
			$mock_client->shouldReceive('headBucket')
				->andReturn(new Result(['BucketName' => 'test-bucket']));

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
							$content = (string)$args['Body'];
							$uploaded_content[$key] = $content;
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
					$content = (string)$args['Body'];
					$uploaded_content[$key] = $content;
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
		$mock_factory->shouldReceive('configure_stream_wrapper')
			->with($mock_client, Mockery::any())
			->andReturnUsing(function($client, $bucket) {
				S3_Media_Sync_Stream_Wrapper::register($client);
				stream_context_set_option(stream_context_get_default(), 's3', 'ACL', $bucket->get_acl());
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
	 * @return Local_File The created local file object.
	 */
	protected function create_temp_file(?string $filename = null, string $content = 'Test content'): Local_File {
		if ($filename === null) {
			$file_path = wp_tempnam();
		} else {
			$upload_dir = wp_upload_dir();
			$target_dir = $upload_dir['path'];
			
			// Create the directory if it doesn't exist
			if (!file_exists($target_dir)) {
				wp_mkdir_p($target_dir);
			}
			
			$file_path = $target_dir . '/' . $filename;
		}

		file_put_contents($file_path, $content);
		return Local_File::from_path($file_path);
	}

	/**
	 * Creates a test upload array for simulating WordPress uploads.
	 *
	 * @param Local_File|string $file The local file object or path.
	 * @param string $mime_type The mime type of the file.
	 * @return array The upload array.
	 */
	protected function create_test_upload($file, string $mime_type): array {
		$file_path = $file instanceof Local_File ? $file->get_path() : $file;
		$file_name = basename($file_path);
		
		$upload_dir = wp_upload_dir();
		$url = $upload_dir['url'] . '/' . $file_name;
		
		return [
			'file' => $file_path,
			'url' => $url,
			'type' => $mime_type
		];
	}

	/**
	 * Creates a test S3 file object.
	 *
	 * @param Local_File $local_file The local file object.
	 * @param S3_Bucket $bucket The S3 bucket object.
	 * @return S3_File The S3 file object.
	 */
	protected function create_test_s3_file(Local_File $local_file, S3_Bucket $bucket): S3_File {
		$uploads = wp_upload_dir();
		$uploads_path = trailingslashit($uploads['basedir']);
		$file_subpath = str_replace($uploads_path, '', $local_file->get_path());
		$s3_key = 'wp-content/uploads/' . $file_subpath;
		
		return S3_File::from_key($bucket, $s3_key);
	}

	/**
	 * Prepares the test environment before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		if ( file_exists( dirname( __FILE__, 2 ) . '/vendor/autoload.php' ) ) {
			require_once dirname( __FILE__, 2 ) . '/vendor/autoload.php';
		}

		$this->default_settings = [
			'bucket' => 'test-bucket',
			'key' => 'test-key',
			'secret' => 'test-secret',
			'region' => 'us-east-1',
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

	/**
	 * Get test settings
	 */
	protected function get_test_settings(): array {
		return [
			'bucket'     => 'test-bucket',
			'key'       => 'test-key',
			'secret'    => 'test-secret',
			'region'    => 'us-east-1',
			'use_acl'   => true,
			'object_acl' => 'public-read',
		];
	}
} 
