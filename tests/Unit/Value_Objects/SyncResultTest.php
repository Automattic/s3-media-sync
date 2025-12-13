<?php
/**
 * Unit tests for Sync_Result value object
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit\Value_Objects;

use S3_Media_Sync\Tests\Unit\TestCase;
use S3_Media_Sync\Value_Objects\Sync_Result;

/**
 * Test case for Sync_Result.
 *
 * @group unit
 * @group value-objects
 * @covers \S3_Media_Sync\Value_Objects\Sync_Result
 */
class SyncResultTest extends TestCase {

	/**
	 * Test uploaded factory creates success result.
	 */
	public function test_uploaded_creates_success_result(): void {
		$result = Sync_Result::uploaded( 123, 'Test Image', 3, array( 'path1', 'path2', 'path3' ) );

		$this->assertSame( 123, $result->get_attachment_id() );
		$this->assertSame( 'Test Image', $result->get_title() );
		$this->assertTrue( $result->is_success() );
		$this->assertSame( Sync_Result::OPERATION_UPLOAD, $result->get_operation() );
		$this->assertSame( 3, $result->get_files_synced() );
		$this->assertSame( 0, $result->get_files_failed() );
		$this->assertNull( $result->get_error() );
		$this->assertFalse( $result->has_error() );
		$this->assertFalse( $result->is_partial() );
		$this->assertCount( 3, $result->get_synced_paths() );
	}

	/**
	 * Test deleted factory creates success result.
	 */
	public function test_deleted_creates_success_result(): void {
		$result = Sync_Result::deleted( 456, 'Another Image', 2, array( 'path1', 'path2' ) );

		$this->assertSame( 456, $result->get_attachment_id() );
		$this->assertSame( 'Another Image', $result->get_title() );
		$this->assertTrue( $result->is_success() );
		$this->assertSame( Sync_Result::OPERATION_DELETE, $result->get_operation() );
		$this->assertSame( 2, $result->get_files_synced() );
		$this->assertSame( 0, $result->get_files_failed() );
	}

	/**
	 * Test failed factory creates failed result.
	 */
	public function test_failed_creates_failed_result(): void {
		$result = Sync_Result::failed( 789, 'Failed Image', Sync_Result::OPERATION_UPLOAD, 'Connection error' );

		$this->assertSame( 789, $result->get_attachment_id() );
		$this->assertSame( 'Failed Image', $result->get_title() );
		$this->assertFalse( $result->is_success() );
		$this->assertSame( Sync_Result::OPERATION_UPLOAD, $result->get_operation() );
		$this->assertSame( 0, $result->get_files_synced() );
		$this->assertSame( 1, $result->get_files_failed() );
		$this->assertSame( 'Connection error', $result->get_error() );
		$this->assertTrue( $result->has_error() );
	}

	/**
	 * Test partial factory creates partial result.
	 */
	public function test_partial_creates_partial_result(): void {
		$failed_paths = array( 'failed1' => 'Error 1', 'failed2' => 'Error 2' );
		$result       = Sync_Result::partial(
			100,
			'Partial Image',
			Sync_Result::OPERATION_UPLOAD,
			2,
			2,
			array( 'path1', 'path2' ),
			$failed_paths
		);

		$this->assertSame( 100, $result->get_attachment_id() );
		$this->assertSame( 'Partial Image', $result->get_title() );
		$this->assertFalse( $result->is_success() );
		$this->assertTrue( $result->is_partial() );
		$this->assertSame( 2, $result->get_files_synced() );
		$this->assertSame( 2, $result->get_files_failed() );
		$this->assertSame( 4, $result->get_total_files() );
		$this->assertCount( 2, $result->get_synced_paths() );
		$this->assertCount( 2, $result->get_failed_paths() );
	}

	/**
	 * Test skipped factory creates skipped result.
	 */
	public function test_skipped_creates_skipped_result(): void {
		$result = Sync_Result::skipped( 200, 'Attachment not found' );

		$this->assertSame( 200, $result->get_attachment_id() );
		$this->assertSame( '', $result->get_title() );
		$this->assertFalse( $result->is_success() );
		$this->assertSame( 0, $result->get_files_synced() );
		$this->assertSame( 0, $result->get_files_failed() );
		$this->assertSame( 0, $result->get_total_files() );
		$this->assertSame( 'Attachment not found', $result->get_error() );
	}

	/**
	 * Test is_partial returns false for full success.
	 */
	public function test_is_partial_returns_false_for_full_success(): void {
		$result = Sync_Result::uploaded( 1, 'Test', 3 );

		$this->assertFalse( $result->is_partial() );
	}

	/**
	 * Test is_partial returns false for full failure.
	 */
	public function test_is_partial_returns_false_for_full_failure(): void {
		$result = Sync_Result::failed( 1, 'Test', Sync_Result::OPERATION_UPLOAD, 'Error' );

		$this->assertFalse( $result->is_partial() );
	}

	/**
	 * Test is_partial returns true when some succeed and some fail.
	 */
	public function test_is_partial_returns_true_for_mixed_results(): void {
		$result = Sync_Result::partial( 1, 'Test', Sync_Result::OPERATION_UPLOAD, 1, 1, array(), array() );

		$this->assertTrue( $result->is_partial() );
	}

	/**
	 * Test get_total_files returns sum of synced and failed.
	 */
	public function test_get_total_files_returns_sum(): void {
		$result = Sync_Result::partial( 1, 'Test', Sync_Result::OPERATION_UPLOAD, 3, 2, array(), array() );

		$this->assertSame( 5, $result->get_total_files() );
	}

	/**
	 * Test to_table_row returns correct array for success.
	 */
	public function test_to_table_row_for_success(): void {
		$result = Sync_Result::uploaded( 1, 'Test Image', 3 );
		$row    = $result->to_table_row();

		$this->assertSame( 1, $row['ID'] );
		$this->assertSame( 'Test Image', $row['Title'] );
		$this->assertSame( 'Success', $row['Status'] );
		$this->assertSame( '3/3', $row['Files'] );
		$this->assertSame( '', $row['Error'] );
	}

	/**
	 * Test to_table_row returns correct array for failure.
	 */
	public function test_to_table_row_for_failure(): void {
		$result = Sync_Result::failed( 2, 'Failed Image', Sync_Result::OPERATION_UPLOAD, 'Upload error' );
		$row    = $result->to_table_row();

		$this->assertSame( 2, $row['ID'] );
		$this->assertSame( 'Failed Image', $row['Title'] );
		$this->assertSame( 'Failed', $row['Status'] );
		$this->assertSame( '0/1', $row['Files'] );
		$this->assertSame( 'Upload error', $row['Error'] );
	}

	/**
	 * Test to_table_row returns correct array for partial.
	 */
	public function test_to_table_row_for_partial(): void {
		$result = Sync_Result::partial( 3, 'Partial Image', Sync_Result::OPERATION_UPLOAD, 2, 1, array(), array() );
		$row    = $result->to_table_row();

		$this->assertSame( 'Partial', $row['Status'] );
		$this->assertSame( '2/3', $row['Files'] );
	}

	/**
	 * Test operation constants have expected values.
	 */
	public function test_operation_constants(): void {
		$this->assertSame( 'upload', Sync_Result::OPERATION_UPLOAD );
		$this->assertSame( 'delete', Sync_Result::OPERATION_DELETE );
	}

	/**
	 * Test has_error returns false when no error.
	 */
	public function test_has_error_returns_false_when_no_error(): void {
		$result = Sync_Result::uploaded( 1, 'Test', 1 );

		$this->assertFalse( $result->has_error() );
	}

	/**
	 * Test has_error returns true when error present.
	 */
	public function test_has_error_returns_true_when_error_present(): void {
		$result = Sync_Result::failed( 1, 'Test', Sync_Result::OPERATION_UPLOAD, 'Error' );

		$this->assertTrue( $result->has_error() );
	}

	/**
	 * Test empty synced paths defaults to empty array.
	 */
	public function test_synced_paths_defaults_to_empty_array(): void {
		$result = Sync_Result::uploaded( 1, 'Test', 0 );

		$this->assertSame( array(), $result->get_synced_paths() );
	}

	/**
	 * Test empty failed paths defaults to empty array.
	 */
	public function test_failed_paths_defaults_to_empty_array(): void {
		$result = Sync_Result::uploaded( 1, 'Test', 1 );

		$this->assertSame( array(), $result->get_failed_paths() );
	}
}
