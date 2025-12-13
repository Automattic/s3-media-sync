<?php
/**
 * Orphan Report Value Object
 *
 * Immutable object representing orphaned files found in S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing a report of orphaned S3 files.
 *
 * @since 2.1.0
 */
class Orphan_Report {

	/**
	 * Array of orphaned files.
	 *
	 * @var array<string, array{key: string, size: int, last_modified: \DateTimeInterface|null}>
	 */
	private array $files;

	/**
	 * Total size in bytes.
	 *
	 * @var int
	 */
	private int $total_size;

	/**
	 * Files grouped by year/month.
	 *
	 * @var array<string, array{count: int, size: int, files: string[]}>
	 */
	private array $grouped;

	/**
	 * The path that was scanned.
	 *
	 * @var string
	 */
	private string $scanned_path;

	/**
	 * Total files scanned in S3.
	 *
	 * @var int
	 */
	private int $total_scanned;

	/**
	 * Constructor.
	 *
	 * @param array<string, array{key: string, size: int, last_modified: \DateTimeInterface|null}> $files         Orphaned files.
	 * @param string                                                                               $scanned_path  Path that was scanned.
	 * @param int                                                                                  $total_scanned Total files scanned.
	 */
	private function __construct( array $files, string $scanned_path, int $total_scanned ) {
		$this->files         = $files;
		$this->scanned_path  = $scanned_path;
		$this->total_scanned = $total_scanned;
		$this->total_size    = 0;
		$this->grouped       = array();

		$this->calculate_statistics();
	}

	/**
	 * Create a new orphan report.
	 *
	 * @since 2.1.0
	 *
	 * @param array<string, array{key: string, size: int, last_modified: \DateTimeInterface|null}> $files         Orphaned files.
	 * @param string                                                                               $scanned_path  Path that was scanned.
	 * @param int                                                                                  $total_scanned Total files scanned.
	 * @return self
	 */
	public static function create( array $files, string $scanned_path = 'wp-content/uploads', int $total_scanned = 0 ): self {
		return new self( $files, $scanned_path, $total_scanned );
	}

	/**
	 * Create an empty report (no orphans found).
	 *
	 * @since 2.1.0
	 *
	 * @param string $scanned_path  Path that was scanned.
	 * @param int    $total_scanned Total files scanned.
	 * @return self
	 */
	public static function empty( string $scanned_path = 'wp-content/uploads', int $total_scanned = 0 ): self {
		return new self( array(), $scanned_path, $total_scanned );
	}

	/**
	 * Calculate total size and group files by year/month.
	 */
	private function calculate_statistics(): void {
		foreach ( $this->files as $relative_key => $file_info ) {
			$this->total_size += $file_info['size'];

			// Extract year/month from path.
			$matches = array();
			if ( preg_match( '|/(\d{4})/(\d{2})/|', $relative_key, $matches ) ) {
				$group = $matches[1] . '/' . $matches[2];
			} else {
				$group = 'other';
			}

			if ( ! isset( $this->grouped[ $group ] ) ) {
				$this->grouped[ $group ] = array(
					'count' => 0,
					'size'  => 0,
					'files' => array(),
				);
			}

			++$this->grouped[ $group ]['count'];
			$this->grouped[ $group ]['size']   += $file_info['size'];
			$this->grouped[ $group ]['files'][] = $relative_key;
		}

		// Sort groups by key (year/month).
		ksort( $this->grouped );
	}

	/**
	 * Get all orphaned files.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, array{key: string, size: int, last_modified: \DateTimeInterface|null}>
	 */
	public function get_files(): array {
		return $this->files;
	}

	/**
	 * Get just the S3 keys of orphaned files.
	 *
	 * @since 2.1.0
	 *
	 * @return string[]
	 */
	public function get_keys(): array {
		return array_column( $this->files, 'key' );
	}

	/**
	 * Get the relative paths of orphaned files.
	 *
	 * @since 2.1.0
	 *
	 * @return string[]
	 */
	public function get_relative_paths(): array {
		return array_keys( $this->files );
	}

	/**
	 * Get total size of orphaned files in bytes.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_total_size(): int {
		return $this->total_size;
	}

	/**
	 * Get formatted total size.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_formatted_size(): string {
		return size_format( $this->total_size, 2 );
	}

	/**
	 * Get files grouped by year/month.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, array{count: int, size: int, files: string[]}>
	 */
	public function get_grouped(): array {
		return $this->grouped;
	}

	/**
	 * Get count of orphaned files.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->files );
	}

	/**
	 * Check if there are any orphaned files.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function has_orphans(): bool {
		return count( $this->files ) > 0;
	}

	/**
	 * Check if the report is empty.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return 0 === count( $this->files );
	}

	/**
	 * Get the path that was scanned.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_scanned_path(): string {
		return $this->scanned_path;
	}

	/**
	 * Get total files scanned in S3.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_total_scanned(): int {
		return $this->total_scanned;
	}

	/**
	 * Get example orphaned files (first N).
	 *
	 * @since 2.1.0
	 *
	 * @param int $count Number of examples to return.
	 * @return string[]
	 */
	public function get_examples( int $count = 10 ): array {
		return array_slice( array_keys( $this->files ), 0, $count );
	}

	/**
	 * Convert grouped data to table rows for display.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $include_total Whether to include a total row.
	 * @return array<array{Group: string, Count: int, Size: string}>
	 */
	public function to_table_rows( bool $include_total = true ): array {
		$rows = array();

		foreach ( $this->grouped as $group => $info ) {
			$rows[] = array(
				'Group' => $group,
				'Count' => $info['count'],
				'Size'  => size_format( $info['size'], 2 ),
			);
		}

		if ( $include_total && ! empty( $this->grouped ) ) {
			$rows[] = array(
				'Group' => 'TOTAL',
				'Count' => $this->count(),
				'Size'  => $this->get_formatted_size(),
			);
		}

		return $rows;
	}
}
