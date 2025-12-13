<?php

use S3_Media_Sync\Services\Cleanup_Service;
use S3_Media_Sync\Services\S3_Repository;
use S3_Media_Sync\Services\Status_Service;
use S3_Media_Sync\Services\Sync_Service;
use S3_Media_Sync\Services\Verify_Service;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Sync_Result;
use S3_Media_Sync\Value_Objects\Verify_Result;
use S3_Media_Sync\Value_Objects\WordPress_Attachment;
use WP_CLI;
use WP_CLI\Utils;
use WP_CLI_Command;

/**
 * Class S3_Media_Sync_WP_CLI_Command
 *
 * Provides WP-CLI commands for managing media uploads to S3.
 *
 * ## EXAMPLES
 *
 *     # Upload a single attachment to S3.
 *     $ wp s3-media upload <attachment_id>
 *
 *     # Upload all validated media to S3.
 *     $ wp s3-media upload-all
 *
 *     # Remove files from S3.
 *     $ wp s3-media rm <path> [--regex=<regex>]
 */
class S3_Media_Sync_WP_CLI_Command extends WP_CLI_Command {

	/**
	 * Upload a single attachment to S3
	 *
	 * @synopsis <attachment_id> [--no-thumbnails]
	 *
	 * ## OPTIONS
	 *
	 * <attachment_id>
	 * : The ID of the attachment to upload to S3.
	 *
	 * [--no-thumbnails]
	 * : Skip uploading thumbnail images.
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload an attachment with ID 123 to S3.
	 *     $ wp s3-media upload 123
	 *
	 *     # Upload only the main file without thumbnails.
	 *     $ wp s3-media upload 123 --no-thumbnails
	 */
	public function upload( $args, $assoc_args ) {
		$attachment_id      = absint( $args[0] );
		$include_thumbnails = ! isset( $assoc_args['no-thumbnails'] );

		if ( 0 === $attachment_id ) {
			WP_CLI::error( 'Invalid attachment ID.' );
		}

		try {
			$attachment = WordPress_Attachment::from_post_id( $attachment_id );
		} catch ( \InvalidArgumentException $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		$service = $this->get_sync_service();
		$result  = $service->upload_attachment( $attachment, $include_thumbnails );

		if ( $result->is_success() ) {
			WP_CLI::success(
				sprintf(
					'Attachment ID %d successfully uploaded to S3 (%d files).',
					$attachment_id,
					$result->get_files_synced()
				) 
			);
		} elseif ( $result->is_partial() ) {
			WP_CLI::warning(
				sprintf(
					'Attachment ID %d partially uploaded: %d/%d files succeeded.',
					$attachment_id,
					$result->get_files_synced(),
					$result->get_total_files()
				) 
			);
		} else {
			WP_CLI::error(
				sprintf(
					'Failed to upload attachment ID %d: %s',
					$attachment_id,
					$result->get_error()
				) 
			);
		}
	}
	
	/**
	 * Upload all validated media to S3
	 *
	 * @subcommand upload-all
	 *
	 * ## OPTIONS
	 *
	 * [--no-thumbnails]
	 * : Skip uploading thumbnail images.
	 *
	 * [--limit=<number>]
	 * : Maximum number of attachments to upload. Default: all.
	 *
	 * [--offset=<number>]
	 * : Number of attachments to skip before starting. Default: 0.
	 *
	 * [--batch-size=<number>]
	 * : Number of attachments to process per batch. Default: 100.
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload all media to S3.
	 *     $ wp s3-media upload-all
	 *
	 *     # Upload first 500 attachments, skipping first 100.
	 *     $ wp s3-media upload-all --limit=500 --offset=100
	 */
	public function upload_all( $args, $assoc_args ) {
		$include_thumbnails = ! isset( $assoc_args['no-thumbnails'] );
		$limit              = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 0;
		$offset             = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;
		$batch_size         = isset( $assoc_args['batch-size'] ) ? absint( $assoc_args['batch-size'] ) : 100;

		$service    = $this->get_sync_service();
		$total      = $service->count_attachments();
		$to_process = 0 === $limit ? $total - $offset : min( $limit, $total - $offset );

		if ( $to_process <= 0 ) {
			WP_CLI::warning( 'No attachments to upload.' );
			return;
		}

		WP_CLI::line( sprintf( 'Uploading %s attachments to S3...', number_format( $to_process ) ) );

		$progress    = Utils\make_progress_bar( 'Uploading', $to_process );
		$all_results = array();
		$processed   = 0;

		while ( $processed < $to_process ) {
			$current_batch_size = min( $batch_size, $to_process - $processed );
			$attachment_ids     = $service->get_attachment_ids( $current_batch_size, $offset + $processed );

			if ( empty( $attachment_ids ) ) {
				break;
			}

			$results = $service->upload_batch(
				$attachment_ids,
				$include_thumbnails,
				static fn() => $progress->tick()
			);

			$all_results = array_merge( $all_results, $results );
			$processed  += count( $attachment_ids );

			// Clear caches to free memory.
			$this->reset_local_object_cache();
			$this->reset_db_query_log();
		}

		$progress->finish();

		// Display summary.
		$summary = $service->get_summary( $all_results );
		$this->render_sync_summary( $summary );
	}

	/**
	 * Remove files from S3
	 *
	 * Props S3 Uploads and HM: https://github.com/humanmade/S3-Uploads/
	 *
	 * @synopsis <path> [--regex=<regex>]
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : The path of the file or directory to remove from S3.
	 *
	 * [--regex=<regex>]
	 * : Optional regex pattern to match files for deletion.
	 *
	 * ## EXAMPLES
	 *
	 *     # Remove a specific file from S3.
	 *     $ wp s3-media rm path/to/file.jpg
	 *
	 *     # Remove all files matching a regex pattern from S3.
	 *     $ wp s3-media rm path/to/files --regex='.*\.jpg'
	 */
	public function rm( $args, $args_assoc ) {
		$s3     = $this->get_s3_media_sync()->get_s3_client();
		$bucket = $this->get_s3_media_sync()->get_s3_bucket();
		$prefix = '';

		if ( strpos( $bucket, '/' ) ) {
			$prefix = trailingslashit( str_replace( strtok( $bucket, '/' ) . '/', '', $bucket ) );
		}

		if ( isset( $args[0] ) ) {
			$prefix .= ltrim( $args[0], '/' );
			if ( strpos( $args[0], '.' ) === false ) {
				$prefix = trailingslashit( $prefix );
			}
		}
		try {
			$objects = $s3->deleteMatchingObjects(
				strtok( $bucket, '/' ),
				$prefix,
				'',
				array(
					'before_delete',
					function () {
						WP_CLI::line( sprintf( 'Deleting file' ) );
					},
				)
			);
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
		WP_CLI::success( sprintf( 'Successfully deleted %s', $prefix ) );
	}

	/**
	 * Get status information about the S3 connection and plugin configuration
	 *
	 * ## EXAMPLES
	 *
	 *     # Check S3 connection status and configuration
	 *     $ wp s3-media status
	 */
	public function status( $args, $assoc_args ) {
		$service = $this->get_status_service();

		// Check if required settings are available.
		if ( ! $service->has_required_settings() ) {
			WP_CLI::error( 'S3 Media Sync is not properly configured. Please set up your AWS credentials in the WordPress admin.' );
			return;
		}

		// Check S3 connection.
		$status = $service->get_connection_status();

		if ( ! $status->is_connected() ) {
			WP_CLI::error( sprintf( 'Failed to connect to S3: %s', $status->get_error() ) );
			return;
		}

		WP_CLI::success( 'Successfully connected to S3' );

		// Display settings summary.
		$settings_data = $service->get_settings_summary();
		Utils\format_items( 'table', $settings_data, array( 'Setting', 'Value' ) );

		// Display AWS identity if available.
		$identity = $service->get_aws_identity();
		if ( null !== $identity ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'AWS Account Information:' );
			WP_CLI::line( '- Account ID: ' . $identity->get_account_id() );
			WP_CLI::line( '- IAM User/Role: ' . $identity->get_arn() );
		}
	}

	/**
	 * Verify local media files against S3
	 *
	 * @synopsis [--verify-size] [--verify-md5] [--fix] [--limit=<number>] [--offset=<number>]
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
	 */
	public function verify( $args, $assoc_args ) {
		// Parse arguments.
		$options = array(
			'verify_size' => isset( $assoc_args['verify-size'] ) ? filter_var( $assoc_args['verify-size'], FILTER_VALIDATE_BOOLEAN ) : true,
			'verify_md5'  => isset( $assoc_args['verify-md5'] ) ? filter_var( $assoc_args['verify-md5'], FILTER_VALIDATE_BOOLEAN ) : false,
		);
		$fix     = isset( $assoc_args['fix'] ) ? filter_var( $assoc_args['fix'], FILTER_VALIDATE_BOOLEAN ) : false;
		$limit   = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 100;
		$offset  = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;

		// Get the verify service.
		$service = $this->get_verify_service();

		// Get attachment IDs to verify.
		$attachment_ids = $service->get_attachment_ids( $limit, $offset );

		if ( empty( $attachment_ids ) ) {
			WP_CLI::warning( 'No attachments found.' );
			return;
		}

		$count_total = count( $attachment_ids );

		// Set up progress bar.
		$progress = \WP_CLI\Utils\make_progress_bar( sprintf( 'Verifying %d attachments', $count_total ), $count_total );

		// Verify attachments.
		$results = $service->verify_batch(
			$attachment_ids,
			$options,
			static fn() => $progress->tick()
		);

		$progress->finish();

		// Fix issues if requested.
		if ( $fix ) {
			$results = array_map(
				fn( Verify_Result $result ) => $result->has_issue() ? $service->fix( $result ) : $result,
				$results
			);
		}

		// Get summary and issues.
		$summary = $service->get_summary( $results );
		$issues  = $service->filter_issues( $results );

		// Report results.
		$this->render_verify_results( $summary, $issues, $options, $fix );
	}

	/**
	 * Render verification results to the console.
	 *
	 * @since 2.1.0
	 *
	 * @param array           $summary Summary statistics.
	 * @param Verify_Result[] $issues  Results with issues.
	 * @param array           $options Verification options.
	 * @param bool            $fix     Whether fix mode was enabled.
	 */
	private function render_verify_results( array $summary, array $issues, array $options, bool $fix ): void {
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

			$table_data   = array_map( fn( Verify_Result $r ) => $r->to_table_row( $fix ), $issues );
			$table_fields = array( 'ID', 'Title', 'Issue', 'Local Size', 'S3 Size' );

			if ( $fix ) {
				$table_fields[] = 'Fixed';
			}

			WP_CLI\Utils\format_items( 'table', $table_data, $table_fields );
		} else {
			WP_CLI::success( 'All verified files are in sync with S3.' );
		}
	}

	/**
	 * Clean up orphaned files in S3 that are not referenced by any WordPress attachments.
	 *
	 * ## OPTIONS
	 *
	 * [--path=<path>]
	 * : The path to check for orphaned files. Defaults to wp-content/uploads.
	 *
	 * [--delete]
	 * : Whether to delete the orphaned files from S3.
	 * By default, this command only shows what would be deleted.
	 *
	 * ## EXAMPLES
	 *
	 *     # Check for orphaned files in the default uploads directory
	 *     $ wp s3-media cleanup
	 *
	 *     # Delete orphaned files from a specific directory
	 *     $ wp s3-media cleanup --path=wp-content/uploads/2025/03 --delete
	 */
	public function cleanup( $args, $assoc_args ) {
		$path   = isset( $assoc_args['path'] ) ? $assoc_args['path'] : 'wp-content/uploads';
		$delete = isset( $assoc_args['delete'] );
		$limit  = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 1000;

		$service = $this->get_cleanup_service();

		WP_CLI::line( sprintf( 'Scanning S3 for orphaned files in "%s"...', $path ) );

		// Find orphaned files with progress callback.
		$progress = Utils\make_progress_bar( 'Scanning', $limit );
		$report   = $service->find_orphaned( $path, $limit, static fn() => $progress->tick() );
		$progress->finish();

		// Display results.
		WP_CLI::line( '' );
		WP_CLI::line( sprintf( 'Scanned %d files, found %d orphaned', $report->get_total_scanned(), $report->count() ) );

		if ( $report->is_empty() ) {
			WP_CLI::success( 'No orphaned files to clean up.' );
			return;
		}

		// Display summary table.
		$table_rows = $report->to_table_rows();
		Utils\format_items( 'table', $table_rows, array( 'Group', 'Count', 'Size' ) );

		// List example files.
		$examples = $report->get_examples( 10 );
		WP_CLI::line( '' );
		WP_CLI::line( 'Example orphaned files:' );
		foreach ( $examples as $example ) {
			WP_CLI::line( ' - ' . $example );
		}

		if ( $report->count() > count( $examples ) ) {
			WP_CLI::line( sprintf( '... and %d more', $report->count() - count( $examples ) ) );
		}

		// Delete orphaned files if requested.
		if ( $delete ) {
			WP_CLI::line( '' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::line( 'DANGER: You are about to delete files!' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::confirm( sprintf( 'Are you sure you want to delete %d orphaned files from S3?', $report->count() ) );

			$delete_progress = Utils\make_progress_bar( 'Deleting orphaned files', $report->count() );
			$result          = $service->delete_orphaned( $report, static fn( $count ) => $delete_progress->tick( $count ) );
			$delete_progress->finish();

			WP_CLI::success( sprintf( 'Successfully deleted %d orphaned files from S3', $result['deleted'] ) );

			if ( $result['failed'] > 0 ) {
				WP_CLI::warning( sprintf( 'Failed to delete %d files', $result['failed'] ) );
				foreach ( $result['errors'] as $key => $error ) {
					WP_CLI::warning( sprintf( 'Failed to delete %s: %s', $key, $error ) );
				}
			}
		} else {
			WP_CLI::line( '' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::line( 'DRY RUN - No files will be deleted' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::line( 'To delete these orphaned files, run this command with the --delete flag:' );
			WP_CLI::line( 'wp s3-media cleanup --path=' . $path . ' --delete' );
		}
	}

	/**
	 * Reset the local WordPress object cache.
	 *
	 * This only cleans the local cache in WP_Object_Cache, without
	 * affecting memcache.
	 */
	private function reset_local_object_cache() {
		global $wp_object_cache;

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$properties = array(
			'group_ops',
			'memcache_debug',
			'cache',
		);

		foreach ( $properties as $property ) {
			if ( property_exists( $wp_object_cache, $property ) ) {
				$wp_object_cache->$property = array();
			}
		}

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}
	}

	/**
	 * Reset the WordPress DB query log.
	 */
	private function reset_db_query_log() {
		global $wpdb;

		$wpdb->queries = array();
	}

	/**
	 * Get the S3 Media Sync instance.
	 *
	 * @since 2.0.0
	 *
	 * @return S3_Media_Sync
	 */
	private function get_s3_media_sync() {
		$settings_handler = new S3_Media_Sync_Settings();
		$s3_media_sync    = new S3_Media_Sync( $settings_handler );
		$s3_media_sync->setup();
		return $s3_media_sync;
	}

	/**
	 * Get the Verify Service instance.
	 *
	 * @since 2.1.0
	 *
	 * @return Verify_Service
	 */
	private function get_verify_service(): Verify_Service {
		$s3         = $this->get_s3_media_sync();
		$settings   = $s3->get_settings_handler()->get_settings();
		$bucket     = S3_Bucket::from_settings( $settings );
		$repository = new S3_Repository( $s3->get_s3_client(), $bucket );

		return new Verify_Service( $repository );
	}

	/**
	 * Get the Cleanup Service instance.
	 *
	 * @since 2.1.0
	 *
	 * @return Cleanup_Service
	 */
	private function get_cleanup_service(): Cleanup_Service {
		$s3         = $this->get_s3_media_sync();
		$settings   = $s3->get_settings_handler()->get_settings();
		$bucket     = S3_Bucket::from_settings( $settings );
		$repository = new S3_Repository( $s3->get_s3_client(), $bucket );

		return new Cleanup_Service( $repository );
	}

	/**
	 * Get the Status Service instance.
	 *
	 * @since 2.1.0
	 *
	 * @return Status_Service
	 */
	private function get_status_service(): Status_Service {
		$s3         = $this->get_s3_media_sync();
		$settings   = $s3->get_settings_handler()->get_settings();
		$bucket     = S3_Bucket::from_settings( $settings );
		$repository = new S3_Repository( $s3->get_s3_client(), $bucket );

		return new Status_Service( $repository, $settings );
	}

	/**
	 * Get the Sync Service instance.
	 *
	 * @since 2.1.0
	 *
	 * @return Sync_Service
	 */
	private function get_sync_service(): Sync_Service {
		$s3         = $this->get_s3_media_sync();
		$settings   = $s3->get_settings_handler()->get_settings();
		$bucket     = S3_Bucket::from_settings( $settings );
		$repository = new S3_Repository( $s3->get_s3_client(), $bucket );

		return new Sync_Service( $repository );
	}

	/**
	 * Render sync operation summary.
	 *
	 * @since 2.1.0
	 *
	 * @param array $summary Summary statistics from get_summary().
	 */
	private function render_sync_summary( array $summary ): void {
		WP_CLI::line( '' );
		WP_CLI::line( 'Sync Summary:' );
		WP_CLI::line( sprintf( '- Total attachments: %d', $summary['total'] ) );
		WP_CLI::line( sprintf( '- Successful: %d', $summary['success'] ) );
		WP_CLI::line( sprintf( '- Failed: %d', $summary['failed'] ) );
		WP_CLI::line( sprintf( '- Skipped: %d', $summary['skipped'] ) );
		WP_CLI::line( sprintf( '- Files synced: %d', $summary['files_synced'] ) );
		WP_CLI::line( sprintf( '- Files failed: %d', $summary['files_failed'] ) );

		if ( $summary['failed'] > 0 ) {
			WP_CLI::warning( sprintf( '%d attachments failed to sync.', $summary['failed'] ) );
		} elseif ( $summary['success'] > 0 ) {
			WP_CLI::success( 'All attachments successfully uploaded to S3.' );
		}
	}

	/**
	 * Upload a file to S3 with proper ACL handling.
	 *
	 * @since 2.0.0
	 *
	 * @param \Aws\S3\S3Client $s3_client      The S3 client.
	 * @param string           $bucket_name    The bucket name.
	 * @param string           $key            The S3 object key.
	 * @param string           $source_file    The local file path.
	 * @return bool Whether the upload succeeded.
	 */
	private function upload_file_to_s3( $s3_client, $bucket_name, $key, $source_file ) {
		$settings_handler = new S3_Media_Sync_Settings();
		$settings         = $settings_handler->get_settings();

		$params = array(
			'Bucket'     => $bucket_name,
			'Key'        => $key,
			'SourceFile' => $source_file,
		);

		// Only add ACL if the setting is enabled
		if ( isset( $settings['use_acl'] ) && $settings['use_acl'] ) {
			$params['ACL'] = isset( $settings['object_acl'] ) ? $settings['object_acl'] : 'public-read';
		}

		try {
			$s3_client->putObject( $params );
			return true;
		} catch ( \Exception $e ) {
			WP_CLI::warning( sprintf( 'Failed to upload %s: %s', $source_file, $e->getMessage() ) );
			return false;
		}
	}
}
