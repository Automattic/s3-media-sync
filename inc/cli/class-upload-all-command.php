<?php
/**
 * Upload All Command
 *
 * WP-CLI command for uploading all attachments to S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\Sync_Service_Interface;
use S3_Media_Sync\Value_Objects\Sync_Result;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Upload all attachments to S3.
 *
 * @since 2.1.0
 */
class Upload_All_Command {

	/**
	 * The sync service.
	 *
	 * @var Sync_Service_Interface
	 */
	private Sync_Service_Interface $sync_service;

	/**
	 * Constructor.
	 *
	 * @param Sync_Service_Interface $sync_service The sync service.
	 */
	public function __construct( Sync_Service_Interface $sync_service ) {
		$this->sync_service = $sync_service;
	}

	/**
	 * Upload all media attachments to S3.
	 *
	 * ## OPTIONS
	 *
	 * [--include-thumbnails]
	 * : Also upload thumbnail versions of attachments.
	 * ---
	 * default: false
	 * ---
	 *
	 * [--force]
	 * : Upload even if files already exist on S3.
	 * ---
	 * default: false
	 * ---
	 *
	 * [--batch-size=<number>]
	 * : Number of attachments to process in each batch.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--limit=<number>]
	 * : Maximum total attachments to upload. 0 for unlimited.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--offset=<number>]
	 * : Number of attachments to skip.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload all media to S3.
	 *     $ wp s3-media upload-all
	 *
	 *     # Upload all media including thumbnails.
	 *     $ wp s3-media upload-all --include-thumbnails
	 *
	 *     # Upload only the first 500 attachments.
	 *     $ wp s3-media upload-all --limit=500
	 *
	 *     # Resume from attachment 1000.
	 *     $ wp s3-media upload-all --offset=1000
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$options = [
			'include_thumbnails' => isset( $assoc_args['include-thumbnails'] ),
			'skip_existing'      => ! isset( $assoc_args['force'] ),
		];

		$batch_size = isset( $assoc_args['batch-size'] ) ? absint( $assoc_args['batch-size'] ) : 100;
		$limit      = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 0;
		$offset     = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;

		// Get total count.
		$total_count = $this->sync_service->count_attachments();

		if ( 0 === $total_count ) {
			WP_CLI::warning( 'No attachments found in the media library.' );
			return;
		}

		// Calculate how many we'll actually process.
		$remaining    = $total_count - $offset;
		$to_process   = $limit > 0 ? min( $limit, $remaining ) : $remaining;

		if ( $to_process <= 0 ) {
			WP_CLI::warning( 'No attachments to process with the given offset.' );
			return;
		}

		WP_CLI::line( sprintf( 'Found %s attachments in the media library.', number_format( $total_count ) ) );
		WP_CLI::line( sprintf( 'Processing %s attachments (offset: %d).', number_format( $to_process ), $offset ) );
		WP_CLI::line( '' );

		$progress = Utils\make_progress_bar( 'Uploading attachments', $to_process );

		$summary = [
			'success' => 0,
			'skipped' => 0,
			'partial' => 0,
			'failed'  => 0,
			'files'   => [
				'uploaded' => 0,
				'skipped'  => 0,
				'failed'   => 0,
			],
		];

		$current_offset = $offset;
		$processed      = 0;

		while ( $processed < $to_process ) {
			$batch_limit    = min( $batch_size, $to_process - $processed );
			$attachment_ids = $this->sync_service->get_attachment_ids( $batch_limit, $current_offset );

			if ( empty( $attachment_ids ) ) {
				break;
			}

			$results = $this->sync_service->upload_batch(
				$attachment_ids,
				$options,
				function ( Sync_Result $result ) use ( $progress, &$summary ) {
					$progress->tick();
					$this->update_summary( $summary, $result );
				}
			);

			$processed      += count( $attachment_ids );
			$current_offset += count( $attachment_ids );

			// Clear caches to free memory.
			$this->clear_caches();
		}

		$progress->finish();

		// Display summary.
		$this->render_summary( $summary );
	}

	/**
	 * Update the summary with a result.
	 *
	 * @param array       $summary The summary array (by reference).
	 * @param Sync_Result $result  The sync result.
	 */
	private function update_summary( array &$summary, Sync_Result $result ): void {
		if ( $result->is_success() ) {
			$summary['success']++;
		} elseif ( $result->is_skipped() ) {
			$summary['skipped']++;
		} elseif ( $result->is_partial() ) {
			$summary['partial']++;
		} else {
			$summary['failed']++;
		}

		$summary['files']['uploaded'] += $result->get_files_uploaded();
		$summary['files']['skipped']  += $result->get_files_skipped();
		$summary['files']['failed']   += $result->get_files_failed();
	}

	/**
	 * Render the final summary.
	 *
	 * @param array $summary The summary data.
	 */
	private function render_summary( array $summary ): void {
		WP_CLI::line( '' );
		WP_CLI::line( 'Upload Summary:' );
		WP_CLI::line( sprintf( '  Attachments: %d success, %d skipped, %d partial, %d failed',
			$summary['success'],
			$summary['skipped'],
			$summary['partial'],
			$summary['failed']
		) );
		WP_CLI::line( sprintf( '  Files: %d uploaded, %d skipped, %d failed',
			$summary['files']['uploaded'],
			$summary['files']['skipped'],
			$summary['files']['failed']
		) );

		if ( $summary['failed'] > 0 ) {
			WP_CLI::warning( sprintf( '%d attachments failed to upload.', $summary['failed'] ) );
		} else {
			WP_CLI::success( 'All attachments processed successfully.' );
		}
	}

	/**
	 * Clear WordPress caches to free memory during batch operations.
	 */
	private function clear_caches(): void {
		global $wp_object_cache, $wpdb;

		// Clear object cache.
		if ( is_object( $wp_object_cache ) ) {
			$properties = [ 'group_ops', 'memcache_debug', 'cache' ];
			foreach ( $properties as $property ) {
				if ( property_exists( $wp_object_cache, $property ) ) {
					$wp_object_cache->$property = [];
				}
			}
			if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
				$wp_object_cache->__remoteset();
			}
		}

		// Clear query log.
		$wpdb->queries = [];
	}
}
