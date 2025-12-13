<?php
/**
 * Sync Service Implementation
 *
 * Provides S3 media sync operations for WordPress attachments.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\Sync_Result;
use S3_Media_Sync\Value_Objects\WordPress_Attachment;

/**
 * Sync Service implementation.
 *
 * @since 2.1.0
 */
class Sync_Service implements Sync_Service_Interface {

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
	 * Upload a single attachment to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment         The attachment to upload.
	 * @param bool                 $include_thumbnails Whether to upload thumbnails.
	 * @return Sync_Result The result of the sync operation.
	 */
	public function upload_attachment( WordPress_Attachment $attachment, bool $include_thumbnails = true ): Sync_Result {
		$files_to_upload = $this->get_files_to_sync( $attachment, $include_thumbnails );

		if ( empty( $files_to_upload ) ) {
			return Sync_Result::skipped( $attachment->get_id(), 'No files to upload.' );
		}

		$synced_paths = array();
		$failed_paths = array();
		$bucket       = $this->repository->get_bucket();

		foreach ( $files_to_upload as $local_file ) {
			$s3_key  = $this->get_s3_key_for_file( $local_file );
			$s3_file = S3_File::from_key( $bucket, $s3_key );

			try {
				$success = $this->repository->put( $local_file, $s3_file );

				if ( $success ) {
					$synced_paths[] = $s3_key;
				} else {
					$failed_paths[ $s3_key ] = 'Upload failed.';
				}
			} catch ( \Exception $e ) {
				$failed_paths[ $s3_key ] = $e->getMessage();
			}
		}

		return $this->create_result(
			$attachment,
			Sync_Result::OPERATION_UPLOAD,
			$synced_paths,
			$failed_paths
		);
	}

	/**
	 * Upload multiple attachments to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param int[]         $attachment_ids     Array of attachment IDs to upload.
	 * @param bool          $include_thumbnails Whether to upload thumbnails.
	 * @param callable|null $progress           Optional callback called after each attachment.
	 * @return Sync_Result[] Array of sync results.
	 */
	public function upload_batch( array $attachment_ids, bool $include_thumbnails = true, ?callable $progress = null ): array {
		$results = array();

		foreach ( $attachment_ids as $attachment_id ) {
			try {
				$attachment = WordPress_Attachment::from_post_id( (int) $attachment_id );
				$result     = $this->upload_attachment( $attachment, $include_thumbnails );
			} catch ( \InvalidArgumentException $e ) {
				$result = Sync_Result::skipped( (int) $attachment_id, $e->getMessage() );
			}

			$results[] = $result;

			if ( null !== $progress ) {
				$progress( $result );
			}
		}

		return $results;
	}

	/**
	 * Delete an attachment from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment         The attachment to delete.
	 * @param bool                 $include_thumbnails Whether to delete thumbnails.
	 * @return Sync_Result The result of the delete operation.
	 */
	public function delete_attachment( WordPress_Attachment $attachment, bool $include_thumbnails = true ): Sync_Result {
		$files_to_delete = $this->get_files_to_sync( $attachment, $include_thumbnails );

		if ( empty( $files_to_delete ) ) {
			return Sync_Result::skipped( $attachment->get_id(), 'No files to delete.' );
		}

		$synced_paths = array();
		$failed_paths = array();
		$bucket       = $this->repository->get_bucket();

		// Collect S3 files for batch deletion.
		$s3_files = array();
		foreach ( $files_to_delete as $local_file ) {
			$s3_key     = $this->get_s3_key_for_file( $local_file );
			$s3_files[] = S3_File::from_key( $bucket, $s3_key );
		}

		// Use batch delete for efficiency.
		$delete_result = $this->repository->delete_batch( $s3_files );

		foreach ( $delete_result['deleted'] as $deleted_file ) {
			$synced_paths[] = $deleted_file->get_key();
		}

		foreach ( $delete_result['failed'] as $failure ) {
			$failed_paths[ $failure['file']->get_key() ] = $failure['error'];
		}

		return $this->create_result(
			$attachment,
			Sync_Result::OPERATION_DELETE,
			$synced_paths,
			$failed_paths
		);
	}

	/**
	 * Check if an attachment exists in S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment The attachment to check.
	 * @return bool True if the attachment exists in S3.
	 */
	public function exists( WordPress_Attachment $attachment ): bool {
		$local_file = $attachment->get_file();
		$s3_key     = $this->get_s3_key_for_file( $local_file );
		$bucket     = $this->repository->get_bucket();
		$s3_file    = S3_File::from_key( $bucket, $s3_key );

		return $this->repository->exists( $s3_file );
	}

	/**
	 * Get attachment IDs for syncing.
	 *
	 * @since 2.1.0
	 *
	 * @param int $limit  Maximum number of IDs to return.
	 * @param int $offset Offset for pagination.
	 * @return int[] Array of attachment IDs.
	 */
	public function get_attachment_ids( int $limit = 100, int $offset = 0 ): array {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->posts is a table name, not user input.
		$sql = $this->wpdb->prepare(
			"SELECT ID FROM {$this->wpdb->posts}
			WHERE post_type = 'attachment'
			ORDER BY ID
			LIMIT %d OFFSET %d",
			$limit,
			$offset
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above.
		$results = $this->wpdb->get_col( $sql );

		return array_map( 'intval', $results );
	}

	/**
	 * Count total attachments in the database.
	 *
	 * @since 2.1.0
	 *
	 * @return int Total number of attachments.
	 */
	public function count_attachments(): int {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->posts is a table name, not user input.
		$sql = "SELECT COUNT(*) FROM {$this->wpdb->posts} WHERE post_type = 'attachment'";
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No user input in query.
		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Get batch sync summary statistics.
	 *
	 * @since 2.1.0
	 *
	 * @param Sync_Result[] $results Array of sync results.
	 * @return array{total: int, success: int, failed: int, skipped: int, files_synced: int, files_failed: int}
	 */
	public function get_summary( array $results ): array {
		$summary = array(
			'total'        => count( $results ),
			'success'      => 0,
			'failed'       => 0,
			'skipped'      => 0,
			'files_synced' => 0,
			'files_failed' => 0,
		);

		foreach ( $results as $result ) {
			if ( $result->is_success() ) {
				++$summary['success'];
			} elseif ( 0 === $result->get_total_files() ) {
				++$summary['skipped'];
			} else {
				++$summary['failed'];
			}

			$summary['files_synced'] += $result->get_files_synced();
			$summary['files_failed'] += $result->get_files_failed();
		}

		return $summary;
	}

	/**
	 * Get files to sync for an attachment.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment         The attachment.
	 * @param bool                 $include_thumbnails Whether to include thumbnails.
	 * @return Local_File[] Array of local files.
	 */
	private function get_files_to_sync( WordPress_Attachment $attachment, bool $include_thumbnails ): array {
		$files = array( $attachment->get_file() );

		if ( $include_thumbnails ) {
			$files = array_merge( $files, array_values( $attachment->get_thumbnails() ) );
		}

		return $files;
	}

	/**
	 * Get the S3 key for a local file.
	 *
	 * @since 2.1.0
	 *
	 * @param Local_File $local_file The local file.
	 * @return string The S3 key.
	 */
	private function get_s3_key_for_file( Local_File $local_file ): string {
		$path = $local_file->get_path();

		// Extract the wp-content/uploads relative path.
		$uploads_dir = wp_upload_dir();
		$base_dir    = $uploads_dir['basedir'];

		if ( 0 === strpos( $path, $base_dir ) ) {
			$relative_path = substr( $path, strlen( $base_dir ) );
			return 'wp-content/uploads' . $relative_path;
		}

		// Fallback to using the full path.
		return $path;
	}

	/**
	 * Create a sync result from operation outcome.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment  $attachment   The attachment.
	 * @param string                $operation    The operation type.
	 * @param string[]              $synced_paths Synced file paths.
	 * @param array<string, string> $failed_paths Failed paths with errors.
	 * @return Sync_Result
	 */
	private function create_result(
		WordPress_Attachment $attachment,
		string $operation,
		array $synced_paths,
		array $failed_paths
	): Sync_Result {
		$files_synced = count( $synced_paths );
		$files_failed = count( $failed_paths );

		if ( 0 === $files_failed ) {
			if ( Sync_Result::OPERATION_UPLOAD === $operation ) {
				return Sync_Result::uploaded(
					$attachment->get_id(),
					$attachment->get_title(),
					$files_synced,
					$synced_paths
				);
			}
			return Sync_Result::deleted(
				$attachment->get_id(),
				$attachment->get_title(),
				$files_synced,
				$synced_paths
			);
		}

		if ( 0 === $files_synced ) {
			$first_error = reset( $failed_paths );
			return Sync_Result::failed(
				$attachment->get_id(),
				$attachment->get_title(),
				$operation,
				false !== $first_error ? $first_error : 'Unknown error.'
			);
		}

		return Sync_Result::partial(
			$attachment->get_id(),
			$attachment->get_title(),
			$operation,
			$files_synced,
			$files_failed,
			$synced_paths,
			$failed_paths
		);
	}
}
