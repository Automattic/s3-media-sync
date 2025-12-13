<?php
/**
 * Unit tests for Verify_Result value object
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit\Value_Objects;

use PHPUnit\Framework\TestCase;
use S3_Media_Sync\Value_Objects\Verify_Result;

/**
 * Test case for Verify_Result.
 *
 * @group unit
 * @group value-objects
 * @covers \S3_Media_Sync\Value_Objects\Verify_Result
 */
class VerifyResultTest extends TestCase {

	/**
	 * Test missing factory creates correct result.
	 */
	public function test_missing_creates_correct_result(): void {
		$result = Verify_Result::missing( 123, 'Test Image', 1024, '/path/to/file.jpg', 'wp-content/uploads/file.jpg' );

		$this->assertSame( 123, $result->get_attachment_id() );
		$this->assertSame( 'Test Image', $result->get_title() );
		$this->assertSame( Verify_Result::ISSUE_MISSING, $result->get_issue_type() );
		$this->assertSame( 'Missing on S3', $result->get_issue_label() );
		$this->assertSame( 1024, $result->get_local_size() );
		$this->assertNull( $result->get_s3_size() );
		$this->assertSame( '/path/to/file.jpg', $result->get_local_path() );
		$this->assertSame( 'wp-content/uploads/file.jpg', $result->get_s3_key() );
		$this->assertTrue( $result->has_issue() );
		$this->assertFalse( $result->was_fixed() );
	}

	/**
	 * Test size_mismatch factory creates correct result.
	 */
	public function test_size_mismatch_creates_correct_result(): void {
		$result = Verify_Result::size_mismatch( 456, 'Another Image', 2048, 1024 );

		$this->assertSame( 456, $result->get_attachment_id() );
		$this->assertSame( 'Another Image', $result->get_title() );
		$this->assertSame( Verify_Result::ISSUE_SIZE_MISMATCH, $result->get_issue_type() );
		$this->assertSame( 'Size mismatch', $result->get_issue_label() );
		$this->assertSame( 2048, $result->get_local_size() );
		$this->assertSame( 1024, $result->get_s3_size() );
		$this->assertTrue( $result->has_issue() );
	}

	/**
	 * Test md5_mismatch factory creates correct result.
	 */
	public function test_md5_mismatch_creates_correct_result(): void {
		$result = Verify_Result::md5_mismatch( 789, 'Third Image', 1024, 1024 );

		$this->assertSame( 789, $result->get_attachment_id() );
		$this->assertSame( 'Third Image', $result->get_title() );
		$this->assertSame( Verify_Result::ISSUE_MD5_MISMATCH, $result->get_issue_type() );
		$this->assertSame( 'MD5 mismatch', $result->get_issue_label() );
		$this->assertSame( 1024, $result->get_local_size() );
		$this->assertSame( 1024, $result->get_s3_size() );
		$this->assertTrue( $result->has_issue() );
	}

	/**
	 * Test ok factory creates correct result.
	 */
	public function test_ok_creates_correct_result(): void {
		$result = Verify_Result::ok( 100, 'Good Image', 5000, 5000 );

		$this->assertSame( 100, $result->get_attachment_id() );
		$this->assertSame( 'Good Image', $result->get_title() );
		$this->assertNull( $result->get_issue_type() );
		$this->assertSame( '', $result->get_issue_label() );
		$this->assertSame( 5000, $result->get_local_size() );
		$this->assertSame( 5000, $result->get_s3_size() );
		$this->assertFalse( $result->has_issue() );
	}

	/**
	 * Test with_fixed returns new instance.
	 */
	public function test_with_fixed_returns_new_instance(): void {
		$original = Verify_Result::missing( 1, 'Test', 100 );
		$fixed    = $original->with_fixed( true );

		$this->assertFalse( $original->was_fixed() );
		$this->assertTrue( $fixed->was_fixed() );
		$this->assertNotSame( $original, $fixed );
	}

	/**
	 * Test with_fixed preserves all other data.
	 */
	public function test_with_fixed_preserves_data(): void {
		$original = Verify_Result::size_mismatch( 42, 'Image', 2000, 1000, '/local/path.jpg', 's3/key.jpg' );
		$fixed    = $original->with_fixed( true );

		$this->assertSame( $original->get_attachment_id(), $fixed->get_attachment_id() );
		$this->assertSame( $original->get_title(), $fixed->get_title() );
		$this->assertSame( $original->get_issue_type(), $fixed->get_issue_type() );
		$this->assertSame( $original->get_local_size(), $fixed->get_local_size() );
		$this->assertSame( $original->get_s3_size(), $fixed->get_s3_size() );
		$this->assertSame( $original->get_local_path(), $fixed->get_local_path() );
		$this->assertSame( $original->get_s3_key(), $fixed->get_s3_key() );
	}

	/**
	 * Test to_table_row returns correct array.
	 */
	public function test_to_table_row_returns_correct_array(): void {
		$result = Verify_Result::missing( 1, 'Test Image', 1024 );
		$row    = $result->to_table_row();

		$this->assertSame( 1, $row['ID'] );
		$this->assertSame( 'Test Image', $row['Title'] );
		$this->assertSame( 'Missing on S3', $row['Issue'] );
		$this->assertSame( '1.00 KB', $row['Local Size'] );
		$this->assertSame( 'N/A', $row['S3 Size'] );
		$this->assertArrayNotHasKey( 'Fixed', $row );
	}

	/**
	 * Test to_table_row includes Fixed column when requested.
	 */
	public function test_to_table_row_includes_fixed_column(): void {
		$result = Verify_Result::missing( 1, 'Test', 1024 )->with_fixed( true );
		$row    = $result->to_table_row( true );

		$this->assertArrayHasKey( 'Fixed', $row );
		$this->assertSame( 'Yes', $row['Fixed'] );
	}

	/**
	 * Test to_table_row shows No for unfixed.
	 */
	public function test_to_table_row_shows_no_for_unfixed(): void {
		$result = Verify_Result::missing( 1, 'Test', 1024 );
		$row    = $result->to_table_row( true );

		$this->assertSame( 'No', $row['Fixed'] );
	}

	/**
	 * Test constants have expected values.
	 */
	public function test_issue_type_constants(): void {
		$this->assertSame( 'missing', Verify_Result::ISSUE_MISSING );
		$this->assertSame( 'size_mismatch', Verify_Result::ISSUE_SIZE_MISMATCH );
		$this->assertSame( 'md5_mismatch', Verify_Result::ISSUE_MD5_MISMATCH );
	}
}
