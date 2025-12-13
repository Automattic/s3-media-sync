<?php
/**
 * Verify Service Implementation
 *
 * Provides verification of attachments against S3 storage.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\Verify_Result;

/**
 * Verify Service implementation.
 *
 * @since 2.1.0
 */
class Verify_Service implements Verify_Service_Interface {

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
	 * Verify a single attachment against S3.
	 *
	 * @since 2.1.0
	 *
	 * @param int   $attachment_id The WordPress attachment ID.
	 * @param array $options {
	 *     Optional. Verification options.
	 *
	 *     @type bool $verify_size Whether to verify file sizes. Default true.
	 *     @type bool $verify_md5  Whether to verify MD5 hashes. Default false.
	 * }
	 * @return Verify_Result|null The verification result, or null if attachment doesn't exist.
	 */
	public function verify_attachment( int $attachment_id, array $options = array() ): ?Verify_Result {
		$verify_size = $options['verify_size'] ?? true;
		$verify_md5  = $options['verify_md5'] ?? false;

		// Get attachment data.
		$attachment = get_post( $attachment_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return null;
		}

		$attachment_url = wp_get_attachment_url( $attachment_id );
		if ( empty( $attachment_url ) ) {
			return null;
		}

		// Get file paths.
		$uploads         = wp_upload_dir();
		$base_dir        = $uploads['basedir'];
		$base_url        = $uploads['baseurl'];
		$relative_path   = str_replace( $base_url, '', $attachment_url );
		$local_file_path = $base_dir . $relative_path;
		$s3_key          = 'wp-content/uploads' . $relative_path;

		// Skip if local file doesn't exist.
		if ( ! file_exists( $local_file_path ) ) {
			return null;
		}

		$local_size = filesize( $local_file_path );
		$title      = $attachment->post_title;

		// Check S3 for the file.
		$bucket  = $this->repository->get_bucket();
		$s3_file = S3_File::from_key( $bucket, $s3_key );

		$metadata = $this->repository->get_metadata( $s3_file );

		// File doesn't exist in S3.
		if ( null === $metadata ) {
			return Verify_Result::missing(
				$attachment_id,
				$title,
				$local_size,
				$local_file_path,
				$s3_key
			);
		}

		$s3_size = $metadata['ContentLength'] ?? 0;
		$s3_etag = $metadata['ETag'] ?? '';

		// Check size mismatch.
		if ( $verify_size && $s3_size !== $local_size ) {
			return Verify_Result::size_mismatch(
				$attachment_id,
				$title,
				$local_size,
				$s3_size,
				$local_file_path,
				$s3_key
			);
		}

		// Check MD5 mismatch.
		if ( $verify_md5 ) {
			$local_md5 = md5_file( $local_file_path );
			if ( $local_md5 !== $s3_etag ) {
				return Verify_Result::md5_mismatch(
					$attachment_id,
					$title,
					$local_size,
					$s3_size,
					$local_file_path,
					$s3_key
				);
			}
		}

		// All checks passed.
		return Verify_Result::ok(
			$attachment_id,
			$title,
			$local_size,
			$s3_size,
			$local_file_path,
			$s3_key
		);
	}

	/**
	 * Verify multiple attachments against S3.
	 *
	 * @since 2.1.0
	 *
	 * @param int[]         $attachment_ids Array of WordPress attachment IDs.
	 * @param array         $options        Verification options (same as verify_attachment).
	 * @param callable|null $progress       Optional callback called after each attachment.
	 * @return Verify_Result[] Array of verification results.
	 */
	public function verify_batch( array $attachment_ids, array $options = array(), ?callable $progress = null ): array {
		$results = array();

		foreach ( $attachment_ids as $attachment_id ) {
			$result = $this->verify_attachment( (int) $attachment_id, $options );

			if ( null !== $result ) {
				$results[] = $result;
			}

			if ( null !== $progress ) {
				$progress( $result );
			}
		}

		return $results;
	}

	/**
	 * Fix a verification issue by re-uploading the file to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Verify_Result $result The verification result to fix.
	 * @return Verify_Result The updated result with fix status.
	 */
	public function fix( Verify_Result $result ): Verify_Result {
		if ( ! $result->has_issue() ) {
			return $result;
		}

		$local_path = $result->get_local_path();
		$s3_key     = $result->get_s3_key();

		if ( null === $local_path || null === $s3_key ) {
			return $result->with_fixed( false );
		}

		if ( ! file_exists( $local_path ) ) {
			return $result->with_fixed( false );
		}

		try {
			$local_file = Local_File::from_path( $local_path );
			$bucket     = $this->repository->get_bucket();
			$s3_file    = S3_File::from_key( $bucket, $s3_key );

			$success = $this->repository->put( $local_file, $s3_file );

			return $result->with_fixed( $success );
		} catch ( \Exception $e ) {
			return $result->with_fixed( false );
		}
	}

	/**
	 * Get attachment IDs for verification.
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

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared above.
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
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->posts is a table name, not user input.
		$sql = "SELECT COUNT(*) FROM {$this->wpdb->posts} WHERE post_type = 'attachment'";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No user input in query.
		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Get verification summary statistics.
	 *
	 * @since 2.1.0
	 *
	 * @param Verify_Result[] $results Array of verification results.
	 * @return array{total: int, missing: int, size_mismatch: int, md5_mismatch: int, ok: int, fixed: int}
	 */
	public function get_summary( array $results ): array {
		$summary = array(
			'total'         => count( $results ),
			'missing'       => 0,
			'size_mismatch' => 0,
			'md5_mismatch'  => 0,
			'ok'            => 0,
			'fixed'         => 0,
		);

		foreach ( $results as $result ) {
			switch ( $result->get_issue_type() ) {
				case Verify_Result::ISSUE_MISSING:
					++$summary['missing'];
					break;
				case Verify_Result::ISSUE_SIZE_MISMATCH:
					++$summary['size_mismatch'];
					break;
				case Verify_Result::ISSUE_MD5_MISMATCH:
					++$summary['md5_mismatch'];
					break;
				default:
					++$summary['ok'];
					break;
			}

			if ( $result->was_fixed() ) {
				++$summary['fixed'];
			}
		}

		return $summary;
	}

	/**
	 * Filter results to only those with issues.
	 *
	 * @since 2.1.0
	 *
	 * @param Verify_Result[] $results Array of verification results.
	 * @return Verify_Result[] Results with issues only.
	 */
	public function filter_issues( array $results ): array {
		return array_filter(
			$results,
			static fn( Verify_Result $result ): bool => $result->has_issue()
		);
	}
}
