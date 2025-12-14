<?php
/**
 * Verify Command
 *
 * WP-CLI command for verifying local media files against S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\Verify_Service;
use S3_Media_Sync\Value_Objects\Verify_Result;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Verify local media files against S3.
 *
 * @since 2.1.0
 */
class Verify_Command {

	/**
	 * The verify service.
	 *
	 * @var Verify_Service
	 */
	private Verify_Service $verify_service;

	/**
	 * Constructor.
	 *
	 * @param Verify_Service $verify_service The verify service.
	 */
	public function __construct( Verify_Service $verify_service ) {
		$this->verify_service = $verify_service;
	}

	/**
	 * Verify local media files against S3.
	 *
	 * ## OPTIONS
	 *
	 * [--verify-size]
	 * : Whether to verify file sizes match
	 * ---
	 * default: true
	 * ---
	 *
	 * [--verify-md5]
	 * : Whether to verify MD5 checksums match (slower but more accurate)
	 * ---
	 * default: false
	 * ---
	 *
	 * [--fix]
	 * : Automatically upload files that don't match or don't exist on S3
	 * ---
	 * default: false
	 * ---
	 *
	 * [--limit=<number>]
	 * : Limit the number of attachments to verify
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--offset=<number>]
	 * : Number of attachments to skip
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Verify file existence and size for 100 attachments
	 *     $ wp s3-media verify
	 *
	 *     # Verify file existence, size, and MD5 checksums, fixing any issues
	 *     $ wp s3-media verify --verify-md5 --fix
	 *
	 *     # Verify a specific batch of attachments
	 *     $ wp s3-media verify --limit=50 --offset=200
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		// Parse arguments.
		$options = [
			'verify_size' => isset( $assoc_args['verify-size'] )
				? filter_var( $assoc_args['verify-size'], FILTER_VALIDATE_BOOLEAN )
				: true,
			'verify_md5'  => isset( $assoc_args['verify-md5'] )
				? filter_var( $assoc_args['verify-md5'], FILTER_VALIDATE_BOOLEAN )
				: false,
		];
		$fix     = isset( $assoc_args['fix'] )
			? filter_var( $assoc_args['fix'], FILTER_VALIDATE_BOOLEAN )
			: false;
		$limit   = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 100;
		$offset  = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;

		// Get attachment IDs to verify.
		$attachment_ids = $this->verify_service->get_attachment_ids( $limit, $offset );

		if ( empty( $attachment_ids ) ) {
			WP_CLI::warning( 'No attachments found.' );
			return;
		}

		$count_total = count( $attachment_ids );

		// Set up progress bar.
		$progress = Utils\make_progress_bar(
			sprintf( 'Verifying %d attachments', $count_total ),
			$count_total
		);

		// Verify attachments.
		$results = $this->verify_service->verify_batch(
			$attachment_ids,
			$options,
			static fn() => $progress->tick()
		);

		$progress->finish();

		// Fix issues if requested.
		if ( $fix ) {
			$results = array_map(
				fn( Verify_Result $result ) => $result->has_issue()
					? $this->verify_service->fix( $result )
					: $result,
				$results
			);
		}

		// Get summary and issues.
		$summary = $this->verify_service->get_summary( $results );
		$issues  = $this->verify_service->filter_issues( $results );

		// Report results.
		$this->render_results( $summary, $issues, $options, $fix );
	}

	/**
	 * Render verification results to the console.
	 *
	 * @param array           $summary Summary statistics.
	 * @param Verify_Result[] $issues  Results with issues.
	 * @param array           $options Verification options.
	 * @param bool            $fix     Whether fix mode was enabled.
	 */
	private function render_results( array $summary, array $issues, array $options, bool $fix ): void {
		WP_CLI::line( '' );
		WP_CLI::line( 'Verification Results:' );
		WP_CLI::line( sprintf( 'Total attachments: %d', $summary['total'] ) );
		WP_CLI::line( sprintf( 'Missing on S3: %d', $summary['missing'] ) );
		WP_CLI::line( sprintf( 'Size mismatches: %d', $summary['size_mismatch'] ) );

		if ( $options['verify_md5'] ) {
			WP_CLI::line( sprintf( 'MD5 mismatches: %d', $summary['md5_mismatch'] ) );
		}

		if ( $fix ) {
			WP_CLI::line( sprintf( 'Issues fixed: %d', $summary['fixed'] ) );
		}

		// Display issues in a table.
		if ( ! empty( $issues ) ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Issues found:' );

			$table_data   = array_map(
				fn( Verify_Result $r ) => $r->to_table_row( $fix ),
				$issues
			);
			$table_fields = [ 'ID', 'Title', 'Issue', 'Local Size', 'S3 Size' ];

			if ( $fix ) {
				$table_fields[] = 'Fixed';
			}

			Utils\format_items( 'table', $table_data, $table_fields );
		} else {
			WP_CLI::success( 'All verified files are in sync with S3.' );
		}
	}
}
