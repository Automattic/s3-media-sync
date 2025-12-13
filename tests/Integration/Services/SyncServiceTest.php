<?php
/**
 * Integration tests for Sync Service
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration\Services;

use Mockery;
use S3_Media_Sync\Services\S3_Repository_Interface;
use S3_Media_Sync\Services\Sync_Service;
use S3_Media_Sync\Services\Sync_Service_Interface;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\Sync_Result;
use S3_Media_Sync\Value_Objects\WordPress_Attachment;

/**
 * Test case for Sync_Service.
 *
 * @group integration
 * @group services
 * @covers \S3_Media_Sync\Services\Sync_Service
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync\Value_Objects\Sync_Result
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\WordPress_Attachment
 */
class SyncServiceTest extends TestCase {

	/**
	 * The sync service instance.
	 *
	 * @var Sync_Service
	 */
	private Sync_Service $service;

	/**
	 * Mock S3 repository.
	 *
	 * @var S3_Repository_Interface|Mockery\MockInterface
	 */
	private $mock_repository;

	/**
	 * Test bucket.
	 *
	 * @var S3_Bucket
	 */
	private S3_Bucket $bucket;

	/**
	 * Set up before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		// Create test bucket.
		$this->bucket = S3_Bucket::from_settings( array(
			'bucket'     => 'test-bucket',
			'region'     => 'us-east-1',
			'use_acl'    => true,
			'object_acl' => 'public-read',
		) );

		// Create mock repository.
		$this->mock_repository = Mockery::mock( S3_Repository_Interface::class );
		$this->mock_repository->shouldReceive( 'get_bucket' )->andReturn( $this->bucket );

		// Create service with mock repository.
		$this->service = new Sync_Service( $this->mock_repository );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}

	/**
	 * Test that service implements interface.
	 */
	public function test_implements_interface(): void {
		$this->assertInstanceOf( Sync_Service_Interface::class, $this->service );
	}

	/**
	 * Test get_attachment_ids returns array of integers.
	 */
	public function test_get_attachment_ids_returns_integers(): void {
		// Create a test attachment.
		$attachment_id = $this->factory()->attachment->create();

		$ids = $this->service->get_attachment_ids( 10, 0 );

		$this->assertIsArray( $ids );
		$this->assertContains( $attachment_id, $ids );
		foreach ( $ids as $id ) {
			$this->assertIsInt( $id );
		}
	}

	/**
	 * Test get_attachment_ids respects limit.
	 */
	public function test_get_attachment_ids_respects_limit(): void {
		// Create multiple attachments.
		$this->factory()->attachment->create_many( 5 );

		$ids = $this->service->get_attachment_ids( 2, 0 );

		$this->assertCount( 2, $ids );
	}

	/**
	 * Test get_attachment_ids respects offset.
	 */
	public function test_get_attachment_ids_respects_offset(): void {
		// Create multiple attachments.
		for ( $i = 0; $i < 5; $i++ ) {
			$this->factory()->attachment->create();
		}

		// Get first batch.
		$first_batch = $this->service->get_attachment_ids( 2, 0 );
		// Get second batch with offset.
		$second_batch = $this->service->get_attachment_ids( 2, 2 );

		// Batches should not overlap.
		$this->assertEmpty( array_intersect( $first_batch, $second_batch ) );
	}

	/**
	 * Test count_attachments returns correct count.
	 */
	public function test_count_attachments_returns_count(): void {
		// Get initial count.
		$initial_count = $this->service->count_attachments();

		// Create new attachments.
		$this->factory()->attachment->create_many( 3 );

		$new_count = $this->service->count_attachments();

		$this->assertSame( $initial_count + 3, $new_count );
	}

	/**
	 * Test upload_attachment returns success result when upload succeeds.
	 */
	public function test_upload_attachment_returns_success_on_upload(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock successful upload.
		$this->mock_repository
			->shouldReceive( 'put' )
			->andReturn( true );

		$result = $this->service->upload_attachment( $attachment, false );

		$this->assertInstanceOf( Sync_Result::class, $result );
		$this->assertTrue( $result->is_success() );
		$this->assertSame( $attachment_id, $result->get_attachment_id() );
	}

	/**
	 * Test upload_attachment returns failed result when upload fails.
	 */
	public function test_upload_attachment_returns_failed_on_error(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock failed upload.
		$this->mock_repository
			->shouldReceive( 'put' )
			->andReturn( false );

		$result = $this->service->upload_attachment( $attachment, false );

		$this->assertInstanceOf( Sync_Result::class, $result );
		$this->assertFalse( $result->is_success() );
	}

	/**
	 * Test upload_attachment handles exceptions.
	 */
	public function test_upload_attachment_handles_exceptions(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock exception.
		$this->mock_repository
			->shouldReceive( 'put' )
			->andThrow( new \Exception( 'Upload error' ) );

		$result = $this->service->upload_attachment( $attachment, false );

		$this->assertFalse( $result->is_success() );
		$this->assertSame( 'Upload error', $result->get_error() );
	}

	/**
	 * Test delete_attachment returns success result.
	 */
	public function test_delete_attachment_returns_success_on_delete(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock successful batch delete.
		$this->mock_repository
			->shouldReceive( 'delete_batch' )
			->andReturnUsing( function ( $files ) {
				return array(
					'deleted' => $files,
					'failed'  => array(),
				);
			} );

		$result = $this->service->delete_attachment( $attachment, false );

		$this->assertInstanceOf( Sync_Result::class, $result );
		$this->assertTrue( $result->is_success() );
		$this->assertSame( Sync_Result::OPERATION_DELETE, $result->get_operation() );
	}

	/**
	 * Test delete_attachment handles failures.
	 */
	public function test_delete_attachment_handles_failures(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock failed batch delete.
		$this->mock_repository
			->shouldReceive( 'delete_batch' )
			->andReturnUsing( function ( $files ) {
				return array(
					'deleted' => array(),
					'failed'  => array_map( function ( $file ) {
						return array(
							'file'  => $file,
							'error' => 'Delete failed',
						);
					}, $files ),
				);
			} );

		$result = $this->service->delete_attachment( $attachment, false );

		$this->assertFalse( $result->is_success() );
	}

	/**
	 * Test exists returns true when file exists.
	 */
	public function test_exists_returns_true_when_file_exists(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock exists check.
		$this->mock_repository
			->shouldReceive( 'exists' )
			->andReturn( true );

		$exists = $this->service->exists( $attachment );

		$this->assertTrue( $exists );
	}

	/**
	 * Test exists returns false when file does not exist.
	 */
	public function test_exists_returns_false_when_file_missing(): void {
		// Create attachment with real file.
		$attachment_id = $this->create_attachment_with_file();
		$attachment    = WordPress_Attachment::from_post_id( $attachment_id );

		// Mock exists check.
		$this->mock_repository
			->shouldReceive( 'exists' )
			->andReturn( false );

		$exists = $this->service->exists( $attachment );

		$this->assertFalse( $exists );
	}

	/**
	 * Test upload_batch calls progress callback.
	 */
	public function test_upload_batch_calls_progress_callback(): void {
		// Create attachments with files.
		$attachment_ids = array();
		for ( $i = 0; $i < 3; $i++ ) {
			$attachment_ids[] = $this->create_attachment_with_file();
		}

		// Mock successful upload.
		$this->mock_repository
			->shouldReceive( 'put' )
			->andReturn( true );

		$callback_count = 0;
		$progress       = function () use ( &$callback_count ) {
			++$callback_count;
		};

		$this->service->upload_batch( $attachment_ids, false, $progress );

		// Callback should be called once per attachment.
		$this->assertSame( 3, $callback_count );
	}

	/**
	 * Test upload_batch handles invalid attachment IDs.
	 */
	public function test_upload_batch_handles_invalid_ids(): void {
		$attachment_ids = array( 999998, 999999 );

		$results = $this->service->upload_batch( $attachment_ids, false );

		$this->assertCount( 2, $results );
		foreach ( $results as $result ) {
			$this->assertFalse( $result->is_success() );
			$this->assertTrue( $result->has_error() );
		}
	}

	/**
	 * Test get_summary calculates correct statistics.
	 */
	public function test_get_summary_calculates_statistics(): void {
		$results = array(
			Sync_Result::uploaded( 1, 'File 1', 2, array( 'path1', 'path2' ) ),
			Sync_Result::uploaded( 2, 'File 2', 3, array( 'path3', 'path4', 'path5' ) ),
			Sync_Result::failed( 3, 'File 3', Sync_Result::OPERATION_UPLOAD, 'Error' ),
			Sync_Result::skipped( 4, 'No file' ),
		);

		$summary = $this->service->get_summary( $results );

		$this->assertSame( 4, $summary['total'] );
		$this->assertSame( 2, $summary['success'] );
		$this->assertSame( 1, $summary['failed'] );
		$this->assertSame( 1, $summary['skipped'] );
		$this->assertSame( 5, $summary['files_synced'] );
		$this->assertSame( 1, $summary['files_failed'] );
	}

	/**
	 * Create an attachment with an actual file.
	 *
	 * @return int The attachment ID.
	 */
	private function create_attachment_with_file(): int {
		// Get uploads directory info.
		$uploads = wp_upload_dir();

		// Create a subdirectory for the test file.
		$subdir = '2024/01';
		$dir    = $uploads['basedir'] . '/' . $subdir;
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Create file with unique name to avoid conflicts.
		$filename     = 'test-file-' . wp_rand() . '.jpg';
		$file_path    = $dir . '/' . $filename;
		$relative_path = $subdir . '/' . $filename;

		// Create the file with test content.
		file_put_contents( $file_path, 'test content' );

		// Create attachment.
		$attachment_id = $this->factory()->attachment->create( array(
			'file' => $file_path,
		) );

		// Set the attached file meta to the relative path.
		update_post_meta( $attachment_id, '_wp_attached_file', $relative_path );

		return $attachment_id;
	}
}
