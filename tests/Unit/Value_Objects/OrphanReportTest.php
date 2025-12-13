<?php
/**
 * Unit tests for Orphan_Report value object
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit\Value_Objects;

use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Orphan_Report;

/**
 * Test case for Orphan_Report value object.
 *
 * @group unit
 * @group value-objects
 * @covers \S3_Media_Sync\Value_Objects\Orphan_Report
 */
class OrphanReportTest extends TestCase {

	/**
	 * Test create factory method.
	 */
	public function test_create_returns_instance(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertInstanceOf( Orphan_Report::class, $report );
	}

	/**
	 * Test empty factory method.
	 */
	public function test_empty_returns_empty_instance(): void {
		$report = Orphan_Report::empty( 'wp-content/uploads', 50 );

		$this->assertInstanceOf( Orphan_Report::class, $report );
		$this->assertTrue( $report->is_empty() );
		$this->assertSame( 0, $report->count() );
		$this->assertSame( 50, $report->get_total_scanned() );
	}

	/**
	 * Test get_files returns all files.
	 */
	public function test_get_files_returns_all_files(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
			'wp-content/uploads/2024/02/file2.jpg' => array(
				'key'           => 'wp-content/uploads/2024/02/file2.jpg',
				'size'          => 2000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertSame( $files, $report->get_files() );
	}

	/**
	 * Test get_keys returns S3 keys.
	 */
	public function test_get_keys_returns_s3_keys(): void {
		$files = array(
			'relative/path/file1.jpg' => array(
				'key'           => 'bucket-prefix/relative/path/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
			'relative/path/file2.jpg' => array(
				'key'           => 'bucket-prefix/relative/path/file2.jpg',
				'size'          => 2000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$keys   = $report->get_keys();

		$this->assertContains( 'bucket-prefix/relative/path/file1.jpg', $keys );
		$this->assertContains( 'bucket-prefix/relative/path/file2.jpg', $keys );
	}

	/**
	 * Test get_relative_paths returns relative paths.
	 */
	public function test_get_relative_paths_returns_relative_paths(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'prefix/wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$paths  = $report->get_relative_paths();

		$this->assertContains( 'wp-content/uploads/2024/01/file1.jpg', $paths );
	}

	/**
	 * Test get_total_size calculates sum of file sizes.
	 */
	public function test_get_total_size_calculates_sum(): void {
		$files = array(
			'file1.jpg' => array(
				'key'           => 'file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
			'file2.jpg' => array(
				'key'           => 'file2.jpg',
				'size'          => 2500,
				'last_modified' => null,
			),
			'file3.jpg' => array(
				'key'           => 'file3.jpg',
				'size'          => 500,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertSame( 4000, $report->get_total_size() );
	}

	/**
	 * Test get_formatted_size returns human readable size.
	 */
	public function test_get_formatted_size_returns_readable(): void {
		$files = array(
			'file1.jpg' => array(
				'key'           => 'file1.jpg',
				'size'          => 1048576, // 1 MB.
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertSame( '1.00 MB', $report->get_formatted_size() );
	}

	/**
	 * Test get_grouped groups files by year/month.
	 */
	public function test_get_grouped_groups_by_year_month(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
			'wp-content/uploads/2024/01/file2.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file2.jpg',
				'size'          => 2000,
				'last_modified' => null,
			),
			'wp-content/uploads/2024/02/file3.jpg' => array(
				'key'           => 'wp-content/uploads/2024/02/file3.jpg',
				'size'          => 1500,
				'last_modified' => null,
			),
		);

		$report  = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$grouped = $report->get_grouped();

		$this->assertArrayHasKey( '2024/01', $grouped );
		$this->assertArrayHasKey( '2024/02', $grouped );
		$this->assertSame( 2, $grouped['2024/01']['count'] );
		$this->assertSame( 3000, $grouped['2024/01']['size'] );
		$this->assertSame( 1, $grouped['2024/02']['count'] );
		$this->assertSame( 1500, $grouped['2024/02']['size'] );
	}

	/**
	 * Test get_grouped uses "other" for non-dated paths.
	 */
	public function test_get_grouped_uses_other_for_non_dated(): void {
		$files = array(
			'wp-content/uploads/some-file.jpg' => array(
				'key'           => 'wp-content/uploads/some-file.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
		);

		$report  = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$grouped = $report->get_grouped();

		$this->assertArrayHasKey( 'other', $grouped );
		$this->assertSame( 1, $grouped['other']['count'] );
	}

	/**
	 * Test count returns file count.
	 */
	public function test_count_returns_file_count(): void {
		$files = array(
			'file1.jpg' => array( 'key' => 'file1.jpg', 'size' => 1000, 'last_modified' => null ),
			'file2.jpg' => array( 'key' => 'file2.jpg', 'size' => 2000, 'last_modified' => null ),
			'file3.jpg' => array( 'key' => 'file3.jpg', 'size' => 3000, 'last_modified' => null ),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertSame( 3, $report->count() );
	}

	/**
	 * Test has_orphans returns true when files exist.
	 */
	public function test_has_orphans_returns_true_when_files_exist(): void {
		$files = array(
			'file1.jpg' => array( 'key' => 'file1.jpg', 'size' => 1000, 'last_modified' => null ),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertTrue( $report->has_orphans() );
	}

	/**
	 * Test has_orphans returns false when empty.
	 */
	public function test_has_orphans_returns_false_when_empty(): void {
		$report = Orphan_Report::empty( 'wp-content/uploads', 100 );

		$this->assertFalse( $report->has_orphans() );
	}

	/**
	 * Test is_empty returns true for empty report.
	 */
	public function test_is_empty_returns_true_for_empty(): void {
		$report = Orphan_Report::create( array(), 'wp-content/uploads', 100 );

		$this->assertTrue( $report->is_empty() );
	}

	/**
	 * Test is_empty returns false when files exist.
	 */
	public function test_is_empty_returns_false_when_files_exist(): void {
		$files = array(
			'file1.jpg' => array( 'key' => 'file1.jpg', 'size' => 1000, 'last_modified' => null ),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );

		$this->assertFalse( $report->is_empty() );
	}

	/**
	 * Test get_scanned_path returns path.
	 */
	public function test_get_scanned_path_returns_path(): void {
		$report = Orphan_Report::create( array(), 'custom/path', 100 );

		$this->assertSame( 'custom/path', $report->get_scanned_path() );
	}

	/**
	 * Test get_total_scanned returns count.
	 */
	public function test_get_total_scanned_returns_count(): void {
		$report = Orphan_Report::create( array(), 'wp-content/uploads', 150 );

		$this->assertSame( 150, $report->get_total_scanned() );
	}

	/**
	 * Test get_examples returns first N files.
	 */
	public function test_get_examples_returns_first_n_files(): void {
		$files = array();
		for ( $i = 1; $i <= 20; $i++ ) {
			$files[ "file{$i}.jpg" ] = array(
				'key'           => "file{$i}.jpg",
				'size'          => $i * 100,
				'last_modified' => null,
			);
		}

		$report   = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$examples = $report->get_examples( 5 );

		$this->assertCount( 5, $examples );
	}

	/**
	 * Test get_examples with count larger than total files.
	 */
	public function test_get_examples_with_large_count(): void {
		$files = array(
			'file1.jpg' => array( 'key' => 'file1.jpg', 'size' => 1000, 'last_modified' => null ),
			'file2.jpg' => array( 'key' => 'file2.jpg', 'size' => 2000, 'last_modified' => null ),
		);

		$report   = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$examples = $report->get_examples( 10 );

		$this->assertCount( 2, $examples );
	}

	/**
	 * Test to_table_rows formats grouped data.
	 */
	public function test_to_table_rows_formats_grouped_data(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1048576, // 1 MB.
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$rows   = $report->to_table_rows();

		$this->assertCount( 2, $rows ); // Data row + total row.

		// Check data row.
		$this->assertSame( '2024/01', $rows[0]['Group'] );
		$this->assertSame( 1, $rows[0]['Count'] );
		$this->assertSame( '1.00 MB', $rows[0]['Size'] );

		// Check total row.
		$this->assertSame( 'TOTAL', $rows[1]['Group'] );
	}

	/**
	 * Test to_table_rows without total.
	 */
	public function test_to_table_rows_without_total(): void {
		$files = array(
			'wp-content/uploads/2024/01/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$rows   = $report->to_table_rows( false );

		$this->assertCount( 1, $rows );
		$this->assertSame( '2024/01', $rows[0]['Group'] );
	}

	/**
	 * Test groups are sorted by key.
	 */
	public function test_groups_are_sorted_by_key(): void {
		$files = array(
			'wp-content/uploads/2024/03/file1.jpg' => array(
				'key'           => 'wp-content/uploads/2024/03/file1.jpg',
				'size'          => 1000,
				'last_modified' => null,
			),
			'wp-content/uploads/2023/12/file2.jpg' => array(
				'key'           => 'wp-content/uploads/2023/12/file2.jpg',
				'size'          => 2000,
				'last_modified' => null,
			),
			'wp-content/uploads/2024/01/file3.jpg' => array(
				'key'           => 'wp-content/uploads/2024/01/file3.jpg',
				'size'          => 3000,
				'last_modified' => null,
			),
		);

		$report = Orphan_Report::create( $files, 'wp-content/uploads', 100 );
		$groups = array_keys( $report->get_grouped() );

		$this->assertSame( array( '2023/12', '2024/01', '2024/03' ), $groups );
	}
}
