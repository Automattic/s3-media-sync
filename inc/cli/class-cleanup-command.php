<?php
/**
 * Cleanup Command
 *
 * WP-CLI command for cleaning up orphaned files in S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\Cleanup_Service_Interface;
use S3_Media_Sync\Value_Objects\Orphan_Report;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Clean up orphaned files in S3.
 *
 * @since 2.1.0
 */
class Cleanup_Command {

	/**
	 * The cleanup service.
	 *
	 * @var Cleanup_Service_Interface
	 */
	private Cleanup_Service_Interface $cleanup_service;

	/**
	 * Constructor.
	 *
	 * @param Cleanup_Service_Interface $cleanup_service The cleanup service.
	 */
	public function __construct( Cleanup_Service_Interface $cleanup_service ) {
		$this->cleanup_service = $cleanup_service;
	}

	/**
	 * Clean up orphaned files in S3 that are not referenced by any WordPress attachments.
	 *
	 * ## OPTIONS
	 *
	 * [--path=<path>]
	 * : The path to check for orphaned files. Defaults to wp-content/uploads.
	 * ---
	 * default: wp-content/uploads
	 * ---
	 *
	 * [--limit=<number>]
	 * : Maximum number of S3 files to scan.
	 * ---
	 * default: 1000
	 * ---
	 *
	 * [--delete]
	 * : Whether to delete the orphaned files from S3.
	 * By default, this command only shows what would be deleted.
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Check for orphaned files in the default uploads directory
	 *     $ wp s3-media cleanup
	 *
	 *     # Check for orphaned files in a specific directory
	 *     $ wp s3-media cleanup --path=wp-content/uploads/2025/03
	 *
	 *     # Delete orphaned files
	 *     $ wp s3-media cleanup --delete
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$path   = $assoc_args['path'] ?? 'wp-content/uploads';
		$limit  = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 1000;
		$delete = isset( $assoc_args['delete'] );

		WP_CLI::line( sprintf( 'Scanning S3 path: %s', $path ) );
		WP_CLI::line( 'Retrieving file list from S3...' );

		// Find orphaned files with progress.
		$progress = Utils\make_progress_bar( 'Scanning files', $limit );
		$report   = $this->cleanup_service->find_orphaned(
			$path,
			$limit,
			static fn() => $progress->tick()
		);
		$progress->finish();

		// Display results.
		$this->render_results( $report );

		// Delete orphaned files if requested.
		if ( $delete && $report->has_orphans() ) {
			$this->delete_orphaned( $report );
		} elseif ( ! $delete && $report->has_orphans() ) {
			$this->render_dry_run_message( $path );
		}
	}

	/**
	 * Render the orphan report results.
	 *
	 * @param Orphan_Report $report The orphan report.
	 */
	private function render_results( Orphan_Report $report ): void {
		WP_CLI::line( '' );
		WP_CLI::line( sprintf( 'Found %d files in S3', $report->get_total_s3_files() ) );
		WP_CLI::line( sprintf( 'Found %d orphaned files', $report->count() ) );

		if ( ! $report->has_orphans() ) {
			WP_CLI::success( 'No orphaned files to clean up.' );
			return;
		}

		// Group by year/month for better reporting.
		$grouped = $report->get_grouped_by_month();

		$table_data = [];
		foreach ( $grouped as $group => $info ) {
			$table_data[] = [
				'Group' => $group,
				'Count' => $info['count'],
				'Size'  => size_format( $info['size'], 2 ),
			];
		}

		// Add total row.
		$table_data[] = [
			'Group' => 'TOTAL',
			'Count' => $report->count(),
			'Size'  => size_format( $report->get_total_size(), 2 ),
		];

		Utils\format_items( 'table', $table_data, [ 'Group', 'Count', 'Size' ] );

		// List some example files.
		$examples = $report->get_examples( 10 );
		WP_CLI::line( '' );
		WP_CLI::line( 'Example orphaned files:' );
		foreach ( $examples as $example ) {
			WP_CLI::line( ' - ' . $example );
		}

		// If there are more files than shown in the examples.
		if ( $report->count() > count( $examples ) ) {
			WP_CLI::line( sprintf( '... and %d more', $report->count() - count( $examples ) ) );
		}
	}

	/**
	 * Delete orphaned files from S3.
	 *
	 * @param Orphan_Report $report The orphan report.
	 */
	private function delete_orphaned( Orphan_Report $report ): void {
		WP_CLI::line( '' );
		WP_CLI::line( '----------------------------------------' );
		WP_CLI::line( 'DANGER: You are about to delete files!' );
		WP_CLI::line( '----------------------------------------' );
		WP_CLI::confirm(
			sprintf( 'Are you sure you want to delete %d orphaned files from S3?', $report->count() )
		);

		$progress = Utils\make_progress_bar( 'Deleting orphaned files', $report->count() );
		$result   = $this->cleanup_service->delete_orphaned(
			$report,
			static fn() => $progress->tick()
		);
		$progress->finish();

		WP_CLI::success( sprintf( 'Successfully deleted %d orphaned files from S3', $result['deleted'] ) );

		if ( $result['failed'] > 0 ) {
			WP_CLI::warning( sprintf( 'Failed to delete %d files', $result['failed'] ) );
			foreach ( $result['errors'] as $error ) {
				WP_CLI::warning( $error );
			}
		}
	}

	/**
	 * Render the dry run message.
	 *
	 * @param string $path The scanned path.
	 */
	private function render_dry_run_message( string $path ): void {
		WP_CLI::line( '' );
		WP_CLI::line( '----------------------------------------' );
		WP_CLI::line( 'DRY RUN - No files will be deleted' );
		WP_CLI::line( '----------------------------------------' );
		WP_CLI::line( 'To delete these orphaned files, run this command with the --delete flag:' );
		WP_CLI::line( 'wp s3-media cleanup --path=' . $path . ' --delete' );
	}
}
