<?php
/**
 * S3 Repository Interface
 *
 * Defines the contract for S3 storage operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use Generator;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;

/**
 * Interface for S3 storage operations.
 *
 * Abstracts all S3 operations to enable testing and allow for
 * alternative implementations.
 *
 * @since 2.1.0
 */
interface S3_Repository_Interface {

	/**
	 * Check if a file exists in S3.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to check.
	 * @return bool True if the file exists, false otherwise.
	 */
	public function exists( S3_File $file ): bool;

	/**
	 * Get metadata for an S3 file.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to get metadata for.
	 * @return array|null The metadata array, or null if file doesn't exist.
	 *                    Keys include: ContentLength, ContentType, ETag, LastModified.
	 */
	public function get_metadata( S3_File $file ): ?array;

	/**
	 * Upload a local file to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Local_File $local   The local file to upload.
	 * @param S3_File    $remote  The target S3 file location.
	 * @param array      $options {
	 *     Optional. Upload options.
	 *
	 *     @type string $acl         The ACL to apply. Default uses bucket settings.
	 *     @type string $content_type The content type. Default uses local file MIME type.
	 * }
	 * @return bool True on success, false on failure.
	 */
	public function put( Local_File $local, S3_File $remote, array $options = [] ): bool;

	/**
	 * Delete a file from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( S3_File $file ): bool;

	/**
	 * Delete multiple files from S3 in a batch operation.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File[] $files Array of S3 files to delete.
	 * @return array{deleted: S3_File[], failed: array{file: S3_File, error: string}[]}
	 */
	public function delete_batch( array $files ): array;

	/**
	 * List files in S3 with a given prefix.
	 *
	 * Uses a generator to efficiently handle large result sets.
	 *
	 * @since 2.1.0
	 *
	 * @param string $prefix The prefix to filter by.
	 * @param int    $limit  Maximum number of files to return. Default 1000.
	 * @return Generator<S3_File> Generator yielding S3_File objects.
	 */
	public function list( string $prefix, int $limit = 1000 ): Generator;

	/**
	 * Check if the configured bucket is accessible.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if the bucket is accessible, false otherwise.
	 */
	public function head_bucket(): bool;

	/**
	 * Get the bucket configuration.
	 *
	 * @since 2.1.0
	 *
	 * @return \S3_Media_Sync\Value_Objects\S3_Bucket The bucket configuration.
	 */
	public function get_bucket(): \S3_Media_Sync\Value_Objects\S3_Bucket;

	/**
	 * Delete all files matching a prefix.
	 *
	 * @since 2.1.0
	 *
	 * @param string        $prefix   The prefix to match files against.
	 * @param string        $regex    Optional regex pattern to filter files.
	 * @param callable|null $progress Optional callback called before each delete.
	 * @return int Number of files deleted.
	 */
	public function delete_by_prefix( string $prefix, string $regex = '', ?callable $progress = null ): int;
}
