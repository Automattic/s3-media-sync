<?php
/**
 * Sync Service Interface
 *
 * Defines the contract for S3 media sync operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Sync_Result;
use S3_Media_Sync\Value_Objects\WordPress_Attachment;

/**
 * Interface for syncing WordPress attachments to S3.
 *
 * @since 2.1.0
 */
interface Sync_Service_Interface {

	/**
	 * Upload a single attachment to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment The attachment to upload.
	 * @param bool                 $include_thumbnails Whether to upload thumbnails.
	 * @return Sync_Result The result of the sync operation.
	 */
	public function upload_attachment( WordPress_Attachment $attachment, bool $include_thumbnails = true ): Sync_Result;

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
	public function upload_batch( array $attachment_ids, bool $include_thumbnails = true, ?callable $progress = null ): array;

	/**
	 * Delete an attachment from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment The attachment to delete.
	 * @param bool                 $include_thumbnails Whether to delete thumbnails.
	 * @return Sync_Result The result of the delete operation.
	 */
	public function delete_attachment( WordPress_Attachment $attachment, bool $include_thumbnails = true ): Sync_Result;

	/**
	 * Check if an attachment exists in S3.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPress_Attachment $attachment The attachment to check.
	 * @return bool True if the attachment exists in S3.
	 */
	public function exists( WordPress_Attachment $attachment ): bool;

	/**
	 * Get attachment IDs for syncing.
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
