<?php
/**
 * Cleanup Service Interface
 *
 * Defines the contract for S3 orphan file cleanup operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Orphan_Report;

/**
 * Interface for finding and cleaning up orphaned S3 files.
 *
 * @since 2.1.0
 */
interface Cleanup_Service_Interface {

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
	public function find_orphaned( string $path = 'wp-content/uploads', int $limit = 1000, ?callable $progress = null ): Orphan_Report;

	/**
	 * Delete orphaned files from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Orphan_Report $report   The orphan report containing files to delete.
	 * @param callable|null $progress Optional callback called after each batch.
	 * @return array{deleted: int, failed: int, errors: array<string, string>}
	 */
	public function delete_orphaned( Orphan_Report $report, ?callable $progress = null ): array;

	/**
	 * Get all WordPress attachment file paths for comparison.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, bool> Map of file paths to true for quick lookup.
	 */
	public function get_wordpress_files(): array;
}
