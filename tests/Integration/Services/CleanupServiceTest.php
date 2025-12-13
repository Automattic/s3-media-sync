<?php
/**
 * Integration tests for Cleanup Service
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration\Services;

use Mockery;
use S3_Media_Sync\Services\Cleanup_Service;
use S3_Media_Sync\Services\Cleanup_Service_Interface;
use S3_Media_Sync\Services\S3_Repository_Interface;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Orphan_Report;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\S3_File;

/**
 * Test case for Cleanup_Service.
 *
 * @group integration
 * @group services
 * @covers \S3_Media_Sync\Services\Cleanup_Service
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\Orphan_Report
 * @uses \S3_Media_Sync\Value_Objects\Region
 */
class CleanupServiceTest extends TestCase {

	/**
	 * The cleanup service instance.
	 *
	 * @var Cleanup_Service
	 */
	private Cleanup_Service $service;

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
		$this->service = new Cleanup_Service( $this->mock_repository );
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
		$this->assertInstanceOf( Cleanup_Service_Interface::class, $this->service );
	}

	/**
	 * Test find_orphaned returns empty report when no S3 files.
	 */
	public function test_find_orphaned_returns_empty_when_no_s3_files(): void {
		// Mock empty list result.
		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( array() ) );

		$report = $this->service->find_orphaned( 'wp-content/uploads', 1000 );

		$this->assertInstanceOf( Orphan_Report::class, $report );
		$this->assertTrue( $report->is_empty() );
		$this->assertSame( 0, $report->count() );
	}

	/**
	 * Test find_orphaned detects orphaned files.
	 */
	public function test_find_orphaned_detects_orphaned_files(): void {
		// Create S3 files in uploads directory.
		$s3_files = array(
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/orphan.jpg', 1000 ),
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/another-orphan.png', 2000 ),
		);

		// Mock list to return these files.
		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( $s3_files ) );

		$report = $this->service->find_orphaned( 'wp-content/uploads', 1000 );

		// Both files should be orphaned since no WordPress attachments exist.
		$this->assertSame( 2, $report->count() );
		$this->assertTrue( $report->has_orphans() );
	}

	/**
	 * Test find_orphaned excludes files in WordPress.
	 */
	public function test_find_orphaned_excludes_wordpress_files(): void {
		// Create a WordPress attachment with a known file path.
		$attachment_id = $this->factory()->attachment->create();
		$file_path     = '2024/01/known-file.jpg';
		update_post_meta( $attachment_id, '_wp_attached_file', $file_path );

		// Create S3 file matching the attachment.
		$s3_file = $this->create_mock_s3_file( 'wp-content/uploads/' . $file_path, 1000 );

		// Also include an orphaned file.
		$orphan_file = $this->create_mock_s3_file( 'wp-content/uploads/2024/01/orphan.jpg', 2000 );

		// Mock list to return both files.
		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( array( $s3_file, $orphan_file ) ) );

		$report = $this->service->find_orphaned( 'wp-content/uploads', 1000 );

		// Only the orphan should be detected.
		$this->assertSame( 1, $report->count() );
		$this->assertContains( 'wp-content/uploads/2024/01/orphan.jpg', $report->get_relative_paths() );
	}

	/**
	 * Test find_orphaned calls progress callback.
	 */
	public function test_find_orphaned_calls_progress_callback(): void {
		$s3_files = array(
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/file1.jpg', 1000 ),
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/file2.jpg', 2000 ),
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/file3.jpg', 3000 ),
		);

		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( $s3_files ) );

		$callback_count = 0;
		$progress       = function () use ( &$callback_count ) {
			++$callback_count;
		};

		$this->service->find_orphaned( 'wp-content/uploads', 1000, $progress );

		// Callback should be called once per file.
		$this->assertSame( 3, $callback_count );
	}

	/**
	 * Test find_orphaned identifies orphaned thumbnails.
	 */
	public function test_find_orphaned_identifies_orphaned_thumbnails(): void {
		// Thumbnail without parent attachment.
		$s3_files = array(
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/missing-150x150.jpg', 1000 ),
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/missing-300x200.jpg', 2000 ),
		);

		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( $s3_files ) );

		$report = $this->service->find_orphaned( 'wp-content/uploads', 1000 );

		// Thumbnails without parent should be orphaned.
		$this->assertSame( 2, $report->count() );
	}

	/**
	 * Test find_orphaned skips files outside uploads directory.
	 */
	public function test_find_orphaned_skips_non_uploads_files(): void {
		$s3_files = array(
			$this->create_mock_s3_file( 'wp-content/plugins/some-file.php', 1000 ),
			$this->create_mock_s3_file( 'wp-content/uploads/2024/01/orphan.jpg', 2000 ),
		);

		$this->mock_repository
			->shouldReceive( 'list' )
			->once()
			->andReturn( $this->create_generator( $s3_files ) );

		$report = $this->service->find_orphaned( 'wp-content/uploads', 1000 );

		// Only the uploads file should be considered.
		$this->assertSame( 1, $report->count() );
		$this->assertContains( 'wp-content/uploads/2024/01/orphan.jpg', $report->get_relative_paths() );
	}

	/**
	 * Test delete_orphaned deletes files successfully.
	 */
	public function test_delete_orphaned_deletes_files(): void {
		$files = array(
			'wp-content/uploads/2024/01/orphan1.jpg' => array(
				'key'  => 'wp-content/uploads/2024/01/orphan1.jpg',
				'size' => 1000,
			),
			'wp-content/uploads/2024/01/orphan2.jpg' => array(
				'key'  => 'wp-content/uploads/2024/01/orphan2.jpg',
				'size' => 2000,
			),
		);
		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		// Mock successful batch delete.
		$this->mock_repository
			->shouldReceive( 'delete_batch' )
			->once()
			->andReturnUsing( function ( $files ) {
				return array(
					'deleted' => $files,
					'failed'  => array(),
				);
			} );

		$result = $this->service->delete_orphaned( $report );

		$this->assertSame( 2, $result['deleted'] );
		$this->assertSame( 0, $result['failed'] );
		$this->assertEmpty( $result['errors'] );
	}

	/**
	 * Test delete_orphaned handles partial failures.
	 */
	public function test_delete_orphaned_handles_failures(): void {
		$files = array(
			'wp-content/uploads/2024/01/orphan1.jpg' => array(
				'key'  => 'wp-content/uploads/2024/01/orphan1.jpg',
				'size' => 1000,
			),
			'wp-content/uploads/2024/01/orphan2.jpg' => array(
				'key'  => 'wp-content/uploads/2024/01/orphan2.jpg',
				'size' => 2000,
			),
		);
		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		// Mock partial failure.
		$this->mock_repository
			->shouldReceive( 'delete_batch' )
			->once()
			->andReturnUsing( function ( $files ) {
				$first_file = array_shift( $files );
				return array(
					'deleted' => array( $first_file ),
					'failed'  => array(
						array(
							'file'  => $files[0],
							'error' => 'Access Denied',
						),
					),
				);
			} );

		$result = $this->service->delete_orphaned( $report );

		$this->assertSame( 1, $result['deleted'] );
		$this->assertSame( 1, $result['failed'] );
		$this->assertNotEmpty( $result['errors'] );
	}

	/**
	 * Test delete_orphaned calls progress callback.
	 */
	public function test_delete_orphaned_calls_progress_callback(): void {
		$files = array(
			'wp-content/uploads/2024/01/orphan1.jpg' => array(
				'key'  => 'wp-content/uploads/2024/01/orphan1.jpg',
				'size' => 1000,
			),
		);
		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->mock_repository
			->shouldReceive( 'delete_batch' )
			->once()
			->andReturn( array(
				'deleted' => array(),
				'failed'  => array(),
			) );

		$callback_count = 0;
		$batch_size     = 0;
		$progress       = function ( $count ) use ( &$callback_count, &$batch_size ) {
			++$callback_count;
			$batch_size = $count;
		};

		$this->service->delete_orphaned( $report, $progress );

		$this->assertSame( 1, $callback_count );
		$this->assertSame( 1, $batch_size );
	}

	/**
	 * Test delete_orphaned returns early for empty report.
	 */
	public function test_delete_orphaned_returns_early_for_empty_report(): void {
		$report = Orphan_Report::empty( 'wp-content/uploads', 100 );

		// Repository should not be called.
		$this->mock_repository->shouldNotReceive( 'delete_batch' );

		$result = $this->service->delete_orphaned( $report );

		$this->assertSame( 0, $result['deleted'] );
		$this->assertSame( 0, $result['failed'] );
		$this->assertEmpty( $result['errors'] );
	}

	/**
	 * Test get_wordpress_files returns expected format.
	 */
	public function test_get_wordpress_files_returns_lookup_map(): void {
		// Create WordPress attachment with a known file path.
		$attachment_id = $this->factory()->attachment->create();
		$file_path     = '2024/03/test-file.jpg';
		update_post_meta( $attachment_id, '_wp_attached_file', $file_path );

		$files = $this->service->get_wordpress_files();

		$this->assertIsArray( $files );
		$this->assertArrayHasKey( $file_path, $files );
		$this->assertTrue( $files[ $file_path ] );

		// Should also have prefixed version.
		$prefixed = 'wp-content/uploads/' . ltrim( $file_path, '/' );
		$this->assertArrayHasKey( $prefixed, $files );
	}

	/**
	 * Create a generator from an array of items.
	 *
	 * @param array $items Items to yield.
	 * @return \Generator
	 */
	private function create_generator( array $items ): \Generator {
		foreach ( $items as $item ) {
			yield $item;
		}
	}

	/**
	 * Create a mock S3_File for testing.
	 *
	 * @param string $key  The S3 key.
	 * @param int    $size The file size.
	 * @return S3_File|Mockery\MockInterface
	 */
	private function create_mock_s3_file( string $key, int $size ) {
		$mock = Mockery::mock( S3_File::class );
		$mock->shouldReceive( 'get_full_path' )->andReturn( $key );
		$mock->shouldReceive( 'get_key' )->andReturn( $key );
		$mock->shouldReceive( 'get_size' )->andReturn( $size );
		return $mock;
	}
}
