<?php
/**
 * Integration tests for S3 Media Sync bulk operations
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
 * Test case for S3 Media Sync bulk operations functionality.
 *
 * @group integration
 * @group bulk-operations
 * @covers S3_Media_Sync
 * @uses S3_Media_Sync_Stream_Wrapper
 */
class BulkOperationsTest extends TestCase {

	/**
	 * Test data for bulk upload scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_bulk_operations(): array {
		$upload_dir = wp_upload_dir();
		$year_month = date( 'Y/m' );
		$base_url = wp_parse_url( $upload_dir['url'] )['path'];
		
		return [
			'multiple images' => [
				[
					'files' => [
						[
							'name' => 'test-image-1.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image-1.jpg',
							'url' => $upload_dir['url'] . '/test-image-1.jpg',
						],
						[
							'name' => 'test-image-2.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image-2.jpg',
							'url' => $upload_dir['url'] . '/test-image-2.jpg',
						],
					],
				],
			],
			'mixed file types' => [
				[
					'files' => [
						[
							'name' => 'test-doc.pdf',
							'type' => 'application/pdf',
							'path' => $upload_dir['path'] . '/test-doc.pdf',
							'url' => $upload_dir['url'] . '/test-doc.pdf',
						],
						[
							'name' => 'test-image.jpg',
							'type' => 'image/jpeg',
							'path' => $upload_dir['path'] . '/test-image.jpg',
							'url' => $upload_dir['url'] . '/test-image.jpg',
						],
					],
				],
			],
		];
	}

	/**
	 * Test bulk upload synchronization with S3.
	 *
	 * @dataProvider data_provider_bulk_operations
	 * 
	 * @param array $test_data The test data.
	 */
	public function test_bulk_upload_syncs_to_s3( array $test_data ): void {
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

		// Mock putObject for each file upload.
		$mock_s3_client->shouldReceive( 'putObject' )
			->withArgs( function ( $args ) use ( $settings ) {
				return $args['Bucket'] === $settings['bucket'] &&
					isset( $args['Key'] ) &&
					isset( $args['Body'] );
			} )
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

		// Create temporary test files and process them.
		foreach ( $test_data['files'] as $file ) {
			// Create the test file
			file_put_contents( $file['path'], 'Test content for ' . $file['name'] );

			// Simulate WordPress upload
			$upload = [
				'file' => $file['path'],
				'url'  => $file['url'],
				'type' => $file['type'],
			];

			// Test the upload sync
			$result = $this->s3_media_sync->add_attachment_to_s3( $upload, 'upload' );

			// Verify the upload was processed
			Assert::assertSame( $upload, $result, 'Upload data should be returned unchanged for ' . $file['name'] );

			// Clean up
			unlink( $file['path'] );
		}
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}
} 
