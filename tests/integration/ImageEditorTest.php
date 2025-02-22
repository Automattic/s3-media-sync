<?php
/**
 * Integration tests for S3 Media Sync image editor functionality
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
use WP_Image_Editor;

/**
 * Test case for S3 Media Sync image editor functionality.
 *
 * @group integration
 * @group image-editor
 * @covers S3_Media_Sync
 * @uses S3_Media_Sync_Stream_Wrapper
 */
class ImageEditorTest extends TestCase {

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
	 * Test image editor synchronization with S3.
	 *
	 * @dataProvider data_provider_image_edits
	 * 
	 * @param array $test_data The test data.
	 */
	public function test_image_editor_changes_sync_to_s3( array $test_data ): void {
		$settings = [
			'bucket'     => 'test-bucket',
			'key'        => 'test-key',
			'secret'     => 'test-secret',
			'region'     => 'test-region',
			'object_acl' => 'public-read',
		];

		// Create a mock S3 client.
		$mock_s3_client = Mockery::mock( S3Client::class );

		// Mock bucket existence check.
		$mock_s3_client->shouldReceive( 'doesBucketExist' )
			->with( $settings['bucket'] )
			->andReturn( true );

		// Mock putObject for the upload.
		$mock_s3_client->shouldReceive( 'putObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] ) &&
					isset( $args['Body'] );
			} )
			->twice()
			->andReturn( new Result( [] ) );

		// Mock stream wrapper methods
		$mock_s3_client->shouldReceive( 'getCommand' )
			->withArgs( function ( $command, $args ) use ( $settings ) {
				return in_array( $command, [ 'PutObject', 'GetObject', 'HeadObject', 'DeleteObject' ], true ) &&
					$args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] );
			} )
			->andReturn( Mockery::mock( [ 'offsetGet' => null, 'offsetExists' => false ] ) );

		$mock_s3_client->shouldReceive( 'execute' )
			->withArgs( function ( $command ) {
				return true;
			} )
			->andReturn( new Result( [] ) );

		// Set up the plugin with mock client.
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			's3',
			$mock_s3_client
		);

		$this->s3_media_sync->setup();

		// Create a temporary test file.
		$upload_dir = wp_upload_dir();
		$test_file_path = $upload_dir['path'] . '/' . $test_data['filename'];
		file_put_contents( $test_file_path, 'Test image content' );

		// Create a mock image editor.
		$mock_image_editor = Mockery::mock( WP_Image_Editor::class );

		// Mock the specific operation
		switch ( $test_data['operation'] ) {
			case 'crop':
				$mock_image_editor->shouldReceive( 'crop' )
					->with(
						$test_data['params']['x'],
						$test_data['params']['y'],
						$test_data['params']['width'],
						$test_data['params']['height']
					)
					->once()
					->andReturn( true );
				break;
			case 'resize':
				$mock_image_editor->shouldReceive( 'resize' )
					->with(
						$test_data['params']['width'],
						$test_data['params']['height']
					)
					->once()
					->andReturn( true );
				break;
			case 'rotate':
				$mock_image_editor->shouldReceive( 'rotate' )
					->with( $test_data['params']['angle'] )
					->once()
					->andReturn( true );
				break;
		}

		// Mock the save operation
		$mock_image_editor->shouldReceive( 'save' )
			->with( $test_file_path, $test_data['mime_type'] )
			->andReturn( [
				'path' => $test_file_path,
				'file' => basename( $test_file_path ),
				'width' => $test_data['operation'] === 'resize' ? $test_data['params']['width'] : 100,
				'height' => $test_data['operation'] === 'resize' ? $test_data['params']['height'] : 100,
				'mime-type' => $test_data['mime_type'],
			] );

		// Perform the image operation before syncing
		switch ( $test_data['operation'] ) {
			case 'crop':
				$mock_image_editor->crop(
					$test_data['params']['x'],
					$test_data['params']['y'],
					$test_data['params']['width'],
					$test_data['params']['height']
				);
				break;
			case 'resize':
				$mock_image_editor->resize(
					$test_data['params']['width'],
					$test_data['params']['height']
				);
				break;
			case 'rotate':
				$mock_image_editor->rotate( $test_data['params']['angle'] );
				break;
		}

		// Test the image editor sync.
		$result = $this->s3_media_sync->add_updated_attachment_to_s3(
			false,
			$test_file_path,
			$mock_image_editor,
			$test_data['mime_type'],
			0
		);

		// Verify the result.
		Assert::assertIsArray( $result, 'Result should be an array with image data' );
		Assert::assertArrayHasKey( 'path', $result, 'Result should contain path' );
		Assert::assertSame( $test_file_path, $result['path'], 'Result path should match input path' );

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
