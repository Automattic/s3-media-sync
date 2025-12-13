<?php
/**
 * Verify Service Interface
 *
 * Defines the contract for attachment verification operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Verify_Result;

/**
 * Interface for verifying attachments between local and S3 storage.
 *
 * @since 2.1.0
 */
interface Verify_Service_Interface {

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
	public function verify_attachment( int $attachment_id, array $options = array() ): ?Verify_Result;

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
	public function verify_batch( array $attachment_ids, array $options = array(), ?callable $progress = null ): array;

	/**
	 * Fix a verification issue by re-uploading the file to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Verify_Result $result The verification result to fix.
	 * @return Verify_Result The updated result with fix status.
	 */
	public function fix( Verify_Result $result ): Verify_Result;

	/**
	 * Get attachment IDs for verification.
	 *
	 * @since 2.1.0
	 *
	 * @param int $limit  Maximum number of IDs to return.
	 * @param int $offset Offset for pagination.
	 * @return int[] Array of attachment IDs.
	 */
	public function get_attachment_ids( int $limit = 100, int $offset = 0 ): array;

	/**
	 * Count total attachments in the database.
	 *
	 * @since 2.1.0
	 *
	 * @return int Total number of attachments.
	 */
	public function count_attachments(): int;
}
