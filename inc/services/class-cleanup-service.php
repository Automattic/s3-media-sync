<?php
/**
 * Cleanup Service Implementation
 *
 * Provides S3 orphan file detection and cleanup operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Orphan_Report;
use S3_Media_Sync\Value_Objects\S3_File;

/**
 * Cleanup Service implementation.
 *
 * @since 2.1.0
 */
class Cleanup_Service implements Cleanup_Service_Interface {

	/**
	 * The S3 repository.
	 *
	 * @var S3_Repository_Interface
	 */
	private S3_Repository_Interface $repository;

	/**
	 * The WordPress database instance.
	 *
	 * @var \wpdb
	 */
	private \wpdb $wpdb;

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_Repository_Interface $repository The S3 repository.
	 * @param \wpdb|null              $wpdb       Optional. The WordPress database instance.
	 */
	public function __construct( S3_Repository_Interface $repository, ?\wpdb $wpdb = null ) {
		$this->repository = $repository;
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
	}

	/**
	 * Find orphaned files in S3 that are not referenced by WordPress attachments.
	 *
	 * @since 2.1.0
	 *
	 * @param string        $path     The path prefix to scan (e.g., 'wp-content/uploads').
	 * @param int           $limit    Maximum number of files to scan.
	 * @param callable|null $progress Optional callback called during scanning.
	 * @return Orphan_Report Report containing orphaned files and statistics.
	 */
	public function find_orphaned( string $path = 'wp-content/uploads', int $limit = 1000, ?callable $progress = null ): Orphan_Report {
		// Get S3 files.
		$s3_files      = $this->list_s3_files( $path, $limit );
		$total_scanned = count( $s3_files );

		if ( empty( $s3_files ) ) {
			return Orphan_Report::empty( $path, 0 );
		}

		// Get WordPress attachment files.
		$wp_files = $this->get_wordpress_files();

		// Find orphaned files.
		$orphaned_files = array();
		$uploads_path   = 'wp-content/uploads';

		foreach ( $s3_files as $relative_key => $file_info ) {
			// Skip if not in uploads directory.
			if ( 0 !== strpos( $relative_key, $uploads_path ) ) {
				if ( null !== $progress ) {
					$progress();
				}
				continue;
			}

			// Get the file path relative to uploads dir.
			$file_path = substr( $relative_key, strlen( $uploads_path ) );
			$file_path = ltrim( $file_path, '/' );

			// Normalize the path.
			$normalized_path = preg_replace( '/\s+/', '-', $file_path );
			$normalized_path = sanitize_file_name( $normalized_path );

			// Check if file is a WordPress-generated thumbnail/variant.
			// These are typically orphaned if the parent is gone.
			if ( preg_match( '/-e\d+\.|-\d+x\d+\./', $normalized_path ) ) {
				// Check if the base file exists in WordPress.
				$base_file = $this->get_base_filename( $file_path );
				if ( ! isset( $wp_files[ $base_file ] ) ) {
					$orphaned_files[ $relative_key ] = $file_info;
				}
				if ( null !== $progress ) {
					$progress();
				}
				continue;
			}

			// Check if file exists in WordPress.
			$check_path = $file_path;
			if ( ! isset( $wp_files[ $check_path ] ) && ! isset( $wp_files[ $relative_key ] ) ) {
				$orphaned_files[ $relative_key ] = $file_info;
			}

			if ( null !== $progress ) {
				$progress();
			}
		}

		return Orphan_Report::create( $orphaned_files, $path, $total_scanned );
	}

	/**
	 * Delete orphaned files from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Orphan_Report $report   The orphan report containing files to delete.
	 * @param callable|null $progress Optional callback called after each batch.
	 * @return array{deleted: int, failed: int, errors: array<string, string>}
	 */
	public function delete_orphaned( Orphan_Report $report, ?callable $progress = null ): array {
		$result = array(
			'deleted' => 0,
			'failed'  => 0,
			'errors'  => array(),
		);

		if ( $report->is_empty() ) {
			return $result;
		}

		$bucket = $this->repository->get_bucket();
		$files  = $report->get_files();

		// Convert to S3_File objects.
		$s3_files = array();
		foreach ( $files as $relative_key => $file_info ) {
			// The key in file_info is the full S3 key.
			$s3_files[ $relative_key ] = S3_File::from_key( $bucket, $file_info['key'] );
		}

		// Process in batches of 1000 (S3 deleteObjects limit).
		$batches = array_chunk( $s3_files, 1000, true );

		foreach ( $batches as $batch ) {
			$delete_result = $this->repository->delete_batch( array_values( $batch ) );

			$result['deleted'] += count( $delete_result['deleted'] );
			$result['failed']  += count( $delete_result['failed'] );

			foreach ( $delete_result['failed'] as $failure ) {
				$key                      = $failure['file']->get_key();
				$result['errors'][ $key ] = $failure['error'];
			}

			if ( null !== $progress ) {
				$progress( count( $batch ) );
			}
		}

		return $result;
	}

	/**
	 * Get all WordPress attachment file paths for comparison.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, bool> Map of file paths to true for quick lookup.
	 */
	public function get_wordpress_files(): array {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->postmeta is a table name, not user input.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $this->wpdb->get_col(
			"SELECT meta_value FROM {$this->wpdb->postmeta} WHERE meta_key = '_wp_attached_file'"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$found_files  = array();
		$uploads_path = 'wp-content/uploads';

		foreach ( $results as $file ) {
			// WordPress stores paths without wp-content/uploads prefix.
			// Store both with and without prefix for comparison.
			$found_files[ $file ]                                     = true;
			$found_files[ $uploads_path . '/' . ltrim( $file, '/' ) ] = true;

			// Also store normalized versions.
			$normalized                 = preg_replace( '/\s+/', '-', $file );
			$normalized                 = sanitize_file_name( $normalized );
			$found_files[ $normalized ] = true;
			$found_files[ $uploads_path . '/' . ltrim( $normalized, '/' ) ] = true;
		}

		return $found_files;
	}

	/**
	 * List files from S3 with the given path prefix.
	 *
	 * @since 2.1.0
	 *
	 * @param string $path  The path prefix to scan.
	 * @param int    $limit Maximum number of files to return.
	 * @return array<string, array{key: string, size: int, last_modified: \DateTimeInterface|null}>
	 */
	private function list_s3_files( string $path, int $limit ): array {
		$bucket = $this->repository->get_bucket();
		$prefix = $bucket->get_prefix();
		$files  = array();

		foreach ( $this->repository->list( $path, $limit ) as $s3_file ) {
			$full_key = $s3_file->get_full_path();

			// Remove bucket prefix to get relative path.
			$relative_key = ! empty( $prefix )
				? substr( $full_key, strlen( trailingslashit( $prefix ) ) )
				: $full_key;

			$files[ $relative_key ] = array(
				'key'           => $full_key,
				'size'          => $s3_file->get_size() ?? 0,
				'last_modified' => null, // Not available from list response.
			);
		}

		return $files;
	}

	/**
	 * Get the base filename from a WordPress-generated variant filename.
	 *
	 * @since 2.1.0
	 *
	 * @param string $filename The variant filename.
	 * @return string The base filename.
	 */
	private function get_base_filename( string $filename ): string {
		// Remove WordPress-generated suffixes like -150x150, -e1234567890, etc.
		$base = preg_replace( '/-\d+x\d+(\.[^.]+)$/', '$1', $filename );
		$base = preg_replace( '/-e\d+(\.[^.]+)$/', '$1', $base );

		return $base;
	}
}
