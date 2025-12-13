<?php
/**
 * Integration tests for Verify Service
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration\Services;

use Mockery;
use S3_Media_Sync\Services\S3_Repository_Interface;
use S3_Media_Sync\Services\Verify_Service;
use S3_Media_Sync\Services\Verify_Service_Interface;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\Verify_Result;

/**
 * Test case for Verify_Service.
 *
 * @group integration
 * @group services
 * @covers \S3_Media_Sync\Services\Verify_Service
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync\Value_Objects\Verify_Result
 * @uses \S3_Media_Sync\Value_Objects\Region
 */
class VerifyServiceTest extends TestCase {

	/**
	 * The verify service instance.
	 *
	 * @var Verify_Service
	 */
	private Verify_Service $service;

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
		$this->bucket = S3_Bucket::from_settings( [
			'bucket'     => 'test-bucket',
			'region'     => 'us-east-1',
			'use_acl'    => true,
			'object_acl' => 'public-read',
		] );

		// Create mock repository.
		$this->mock_repository = Mockery::mock( S3_Repository_Interface::class );
		$this->mock_repository->shouldReceive( 'get_bucket' )->andReturn( $this->bucket );

		// Create service with mock repository.
		$this->service = new Verify_Service( $this->mock_repository );
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
		$this->assertInstanceOf( Verify_Service_Interface::class, $this->service );
	}

	/**
	 * Test verify_attachment returns null for non-existent attachment.
	 */
	public function test_verify_attachment_returns_null_for_invalid_id(): void {
		$result = $this->service->verify_attachment( 999999 );

		$this->assertNull( $result );
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
		$attachment_ids = [];
		for ( $i = 0; $i < 5; $i++ ) {
			$attachment_ids[] = $this->factory()->attachment->create();
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
	 * Test verify_batch calls progress callback.
	 */
	public function test_verify_batch_calls_progress_callback(): void {
		$attachment_ids = $this->factory()->attachment->create_many( 3 );

		// Mock repository to return null metadata (file missing).
		$this->mock_repository->shouldReceive( 'get_metadata' )->andReturn( null );

		$callback_count = 0;
		$progress       = function () use ( &$callback_count ) {
			++$callback_count;
		};

		$this->service->verify_batch( $attachment_ids, [], $progress );

		// Callback should be called once per attachment.
		$this->assertSame( 3, $callback_count );
	}

	/**
	 * Test get_summary calculates correct statistics.
	 */
	public function test_get_summary_calculates_statistics(): void {
		$results = [
			Verify_Result::missing( 1, 'File 1', 1000 ),
			Verify_Result::missing( 2, 'File 2', 2000 ),
			Verify_Result::size_mismatch( 3, 'File 3', 1000, 900 ),
			Verify_Result::md5_mismatch( 4, 'File 4', 1000, 1000 ),
			Verify_Result::ok( 5, 'File 5', 1000, 1000 ),
			Verify_Result::ok( 6, 'File 6', 2000, 2000 ),
		];

		$summary = $this->service->get_summary( $results );

		$this->assertSame( 6, $summary['total'] );
		$this->assertSame( 2, $summary['missing'] );
		$this->assertSame( 1, $summary['size_mismatch'] );
		$this->assertSame( 1, $summary['md5_mismatch'] );
		$this->assertSame( 2, $summary['ok'] );
		$this->assertSame( 0, $summary['fixed'] );
	}

	/**
	 * Test get_summary counts fixed results.
	 */
	public function test_get_summary_counts_fixed(): void {
		$results = [
			Verify_Result::missing( 1, 'File 1', 1000 )->with_fixed( true ),
			Verify_Result::missing( 2, 'File 2', 2000 )->with_fixed( true ),
			Verify_Result::size_mismatch( 3, 'File 3', 1000, 900 )->with_fixed( false ),
		];

		$summary = $this->service->get_summary( $results );

		$this->assertSame( 2, $summary['fixed'] );
	}

	/**
	 * Test filter_issues returns only results with issues.
	 */
	public function test_filter_issues_returns_only_issues(): void {
		$results = [
			Verify_Result::missing( 1, 'File 1', 1000 ),
			Verify_Result::ok( 2, 'File 2', 1000, 1000 ),
			Verify_Result::size_mismatch( 3, 'File 3', 1000, 900 ),
			Verify_Result::ok( 4, 'File 4', 2000, 2000 ),
		];

		$issues = $this->service->filter_issues( $results );

		$this->assertCount( 2, $issues );
		foreach ( $issues as $issue ) {
			$this->assertTrue( $issue->has_issue() );
		}
	}

	/**
	 * Test fix returns result with fixed status when successful.
	 */
	public function test_fix_returns_fixed_result_on_success(): void {
		// Create a temporary file for testing.
		$temp_file = wp_tempnam( 'test' );
		file_put_contents( $temp_file, 'test content' );

		$result = Verify_Result::missing(
			1,
			'Test File',
			12,
			$temp_file,
			'wp-content/uploads/test.txt'
		);

		// Mock successful upload.
		$this->mock_repository
			->shouldReceive( 'put' )
			->once()
			->andReturn( true );

		$fixed_result = $this->service->fix( $result );

		$this->assertTrue( $fixed_result->was_fixed() );

		// Cleanup.
		unlink( $temp_file );
	}

	/**
	 * Test fix returns unfixed result when upload fails.
	 */
	public function test_fix_returns_unfixed_result_on_failure(): void {
		// Create a temporary file for testing.
		$temp_file = wp_tempnam( 'test' );
		file_put_contents( $temp_file, 'test content' );

		$result = Verify_Result::missing(
			1,
			'Test File',
			12,
			$temp_file,
			'wp-content/uploads/test.txt'
		);

		// Mock failed upload.
		$this->mock_repository
			->shouldReceive( 'put' )
			->once()
			->andReturn( false );

		$fixed_result = $this->service->fix( $result );

		$this->assertFalse( $fixed_result->was_fixed() );

		// Cleanup.
		unlink( $temp_file );
	}

	/**
	 * Test fix does nothing for results without issues.
	 */
	public function test_fix_does_nothing_for_ok_results(): void {
		$result = Verify_Result::ok( 1, 'Test File', 1000, 1000 );

		// Repository should not be called.
		$this->mock_repository->shouldNotReceive( 'put' );

		$fixed_result = $this->service->fix( $result );

		$this->assertFalse( $fixed_result->was_fixed() );
	}

	/**
	 * Test fix returns unfixed when local file doesn't exist.
	 */
	public function test_fix_returns_unfixed_when_file_missing(): void {
		$result = Verify_Result::missing(
			1,
			'Test File',
			1000,
			'/nonexistent/path/file.txt',
			'wp-content/uploads/file.txt'
		);

		// Repository should not be called.
		$this->mock_repository->shouldNotReceive( 'put' );

		$fixed_result = $this->service->fix( $result );

		$this->assertFalse( $fixed_result->was_fixed() );
	}
}
