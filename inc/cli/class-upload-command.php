<?php
/**
 * Upload Command
 *
 * WP-CLI command for uploading a single attachment to S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\Sync_Service_Interface;
use WP_CLI;

/**
 * Upload a single attachment to S3.
 *
 * @since 2.1.0
 */
class Upload_Command {

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
	 * Upload a single attachment to S3.
	 *
	 * ## OPTIONS
	 *
	 * <attachment_id>
	 * : The ID of the attachment to upload to S3.
	 *
	 * [--include-thumbnails]
	 * : Also upload thumbnail versions of the attachment.
	 * ---
	 * default: false
	 * ---
	 *
	 * [--force]
	 * : Upload even if the file already exists on S3.
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload an attachment with ID 123 to S3.
	 *     $ wp s3-media upload 123
	 *
	 *     # Upload an attachment with all its thumbnails.
	 *     $ wp s3-media upload 123 --include-thumbnails
	 *
	 *     # Force re-upload even if file exists.
	 *     $ wp s3-media upload 123 --force
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$attachment_id = absint( $args[0] ?? 0 );

		if ( 0 === $attachment_id ) {
			WP_CLI::error( 'Invalid attachment ID.' );
		}

		$options = [
			'include_thumbnails' => isset( $assoc_args['include-thumbnails'] ),
			'skip_existing'      => ! isset( $assoc_args['force'] ),
		];

		WP_CLI::line( sprintf( 'Uploading attachment %d to S3...', $attachment_id ) );

		$result = $this->sync_service->upload_attachment( $attachment_id, $options );

		if ( $result->is_success() ) {
			WP_CLI::success(
				sprintf(
					'Attachment %d uploaded successfully. Files: %d uploaded, %d skipped.',
					$attachment_id,
					$result->get_files_uploaded(),
					$result->get_files_skipped()
				)
			);
		} elseif ( $result->is_skipped() ) {
			WP_CLI::warning(
				sprintf(
					'Attachment %d skipped: %s',
					$attachment_id,
					$result->get_error() ?? 'All files already exist on S3'
				)
			);
		} elseif ( $result->is_partial() ) {
			WP_CLI::warning(
				sprintf(
					'Attachment %d partially uploaded. Files: %d uploaded, %d failed, %d skipped.',
					$attachment_id,
					$result->get_files_uploaded(),
					$result->get_files_failed(),
					$result->get_files_skipped()
				)
			);
		} else {
			WP_CLI::error(
				sprintf(
					'Failed to upload attachment %d: %s',
					$attachment_id,
					$result->get_error() ?? 'Unknown error'
				)
			);
		}
	}
}
