<?php
/**
 * S3 Repository Implementation
 *
 * Provides concrete implementation of S3 storage operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Generator;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\S3_File;

/**
 * S3 Repository implementation using AWS SDK.
 *
 * @since 2.1.0
 */
class S3_Repository implements S3_Repository_Interface {

	/**
	 * The AWS S3 client.
	 *
	 * @var S3Client
	 */
	private S3Client $client;

	/**
	 * The S3 bucket configuration.
	 *
	 * @var S3_Bucket
	 */
	private S3_Bucket $bucket;

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param S3Client  $client The AWS S3 client.
	 * @param S3_Bucket $bucket The S3 bucket configuration.
	 */
	public function __construct( S3Client $client, S3_Bucket $bucket ) {
		$this->client = $client;
		$this->bucket = $bucket;
	}

	/**
	 * Check if a file exists in S3.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to check.
	 * @return bool True if the file exists, false otherwise.
	 */
	public function exists( S3_File $file ): bool {
		try {
			$this->client->headObject( $this->get_object_params( $file ) );
			return true;
		} catch ( S3Exception $e ) {
			return false;
		}
	}

	/**
	 * Get metadata for an S3 file.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to get metadata for.
	 * @return array|null The metadata array, or null if file doesn't exist.
	 */
	public function get_metadata( S3_File $file ): ?array {
		try {
			$result = $this->client->headObject( $this->get_object_params( $file ) );

			return [
				'ContentLength' => $result['ContentLength'] ?? null,
				'ContentType'   => $result['ContentType'] ?? null,
				'ETag'          => isset( $result['ETag'] ) ? trim( $result['ETag'], '"' ) : null,
				'LastModified'  => $result['LastModified'] ?? null,
			];
		} catch ( S3Exception $e ) {
			return null;
		}
	}

	/**
	 * Upload a local file to S3.
	 *
	 * @since 2.1.0
	 *
	 * @param Local_File $local   The local file to upload.
	 * @param S3_File    $remote  The target S3 file location.
	 * @param array      $options Upload options.
	 * @return bool True on success, false on failure.
	 */
	public function put( Local_File $local, S3_File $remote, array $options = [] ): bool {
		if ( ! $local->exists() ) {
			return false;
		}

		$params = array_merge(
			$this->get_object_params( $remote ),
			[
				'SourceFile'  => $local->get_path(),
				'ContentType' => $options['content_type'] ?? $local->get_mime_type(),
			]
		);

		// Handle ACL - use option, fall back to bucket settings.
		if ( isset( $options['acl'] ) ) {
			$params['ACL'] = $options['acl'];
		} elseif ( $this->bucket->should_use_acl() && $this->bucket->get_object_acl() !== null ) {
			$params['ACL'] = $this->bucket->get_object_acl();
		}

		try {
			$this->client->putObject( $params );
			return true;
		} catch ( S3Exception $e ) {
			// If ACL is not supported, retry without it.
			if ( $this->is_acl_not_supported_error( $e ) && isset( $params['ACL'] ) ) {
				unset( $params['ACL'] );
				try {
					$this->client->putObject( $params );
					return true;
				} catch ( S3Exception $retry_e ) {
					return false;
				}
			}
			return false;
		}
	}

	/**
	 * Delete a file from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File $file The S3 file to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( S3_File $file ): bool {
		try {
			$this->client->deleteObject( $this->get_object_params( $file ) );
			return true;
		} catch ( S3Exception $e ) {
			return false;
		}
	}

	/**
	 * Delete multiple files from S3 in a batch operation.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_File[] $files Array of S3 files to delete.
	 * @return array{deleted: S3_File[], failed: array{file: S3_File, error: string}[]}
	 */
	public function delete_batch( array $files ): array {
		if ( empty( $files ) ) {
			return [
				'deleted' => [],
				'failed'  => [],
			];
		}

		$objects = [];
		$file_map = [];

		foreach ( $files as $file ) {
			$key = $this->bucket->get_full_path( $file->get_key() );
			$objects[] = [ 'Key' => $key ];
			$file_map[ $key ] = $file;
		}

		// S3 deleteObjects has a limit of 1000 objects per request.
		$batches = array_chunk( $objects, 1000 );
		$deleted = [];
		$failed = [];

		foreach ( $batches as $batch ) {
			try {
				$result = $this->client->deleteObjects( [
					'Bucket' => $this->bucket->get_name(),
					'Delete' => [
						'Objects' => $batch,
						'Quiet'   => false,
					],
				] );

				// Track deleted files.
				if ( ! empty( $result['Deleted'] ) ) {
					foreach ( $result['Deleted'] as $item ) {
						if ( isset( $file_map[ $item['Key'] ] ) ) {
							$deleted[] = $file_map[ $item['Key'] ];
						}
					}
				}

				// Track failed files.
				if ( ! empty( $result['Errors'] ) ) {
					foreach ( $result['Errors'] as $error ) {
						if ( isset( $file_map[ $error['Key'] ] ) ) {
							$failed[] = [
								'file'  => $file_map[ $error['Key'] ],
								'error' => $error['Message'] ?? 'Unknown error',
							];
						}
					}
				}
			} catch ( S3Exception $e ) {
				// If the entire batch fails, mark all files as failed.
				foreach ( $batch as $item ) {
					if ( isset( $file_map[ $item['Key'] ] ) ) {
						$failed[] = [
							'file'  => $file_map[ $item['Key'] ],
							'error' => $e->getMessage(),
						];
					}
				}
			}
		}

		return [
			'deleted' => $deleted,
			'failed'  => $failed,
		];
	}

	/**
	 * List files in S3 with a given prefix.
	 *
	 * @since 2.1.0
	 *
	 * @param string $prefix The prefix to filter by.
	 * @param int    $limit  Maximum number of files to return.
	 * @return Generator<S3_File> Generator yielding S3_File objects.
	 */
	public function list( string $prefix, int $limit = 1000 ): Generator {
		$full_prefix = $this->bucket->get_full_path( $prefix );
		$count = 0;
		$continuation_token = null;

		do {
			$params = [
				'Bucket'  => $this->bucket->get_name(),
				'Prefix'  => $full_prefix,
				'MaxKeys' => min( 1000, $limit - $count ),
			];

			if ( $continuation_token !== null ) {
				$params['ContinuationToken'] = $continuation_token;
			}

			try {
				$result = $this->client->listObjectsV2( $params );
			} catch ( S3Exception $e ) {
				return;
			}

			$contents = $result['Contents'] ?? [];

			foreach ( $contents as $object ) {
				// Skip directory markers.
				if ( substr( $object['Key'], -1 ) === '/' ) {
					continue;
				}

				// Get the key relative to the bucket prefix.
				$relative_key = $this->bucket->get_key_from_path( $object['Key'] );

				yield S3_File::from_metadata(
					$this->bucket,
					$relative_key,
					[
						'ETag'          => $object['ETag'] ?? null,
						'ContentLength' => $object['Size'] ?? null,
						'ContentType'   => null, // Not available in list response.
					]
				);

				$count++;
				if ( $count >= $limit ) {
					return;
				}
			}

			$continuation_token = $result['NextContinuationToken'] ?? null;
		} while ( ! empty( $result['IsTruncated'] ) && $count < $limit );
	}

	/**
	 * Check if the configured bucket is accessible.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if the bucket is accessible, false otherwise.
	 */
	public function head_bucket(): bool {
		try {
			$this->client->headBucket( [
				'Bucket' => $this->bucket->get_name(),
			] );
			return true;
		} catch ( S3Exception $e ) {
			return false;
		}
	}

	/**
	 * Get the S3 client instance.
	 *
	 * Useful for advanced operations not covered by the interface.
	 *
	 * @since 2.1.0
	 *
	 * @return S3Client The AWS S3 client.
	 */
	public function get_client(): S3Client {
		return $this->client;
	}

	/**
	 * Get the bucket configuration.
	 *
	 * @since 2.1.0
	 *
	 * @return S3_Bucket The bucket configuration.
	 */
	public function get_bucket(): S3_Bucket {
		return $this->bucket;
	}

	/**
	 * Get the object parameters for an S3 file.
	 *
	 * @param S3_File $file The S3 file.
	 * @return array The object parameters.
	 */
	private function get_object_params( S3_File $file ): array {
		return [
			'Bucket' => $this->bucket->get_name(),
			'Key'    => $this->bucket->get_full_path( $file->get_key() ),
		];
	}

	/**
	 * Check if an S3 exception is an ACL not supported error.
	 *
	 * @param S3Exception $e The exception to check.
	 * @return bool True if the error is ACL not supported.
	 */
	private function is_acl_not_supported_error( S3Exception $e ): bool {
		return strpos( $e->getMessage(), 'AccessControlListNotSupported' ) !== false;
	}

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
	public function delete_by_prefix( string $prefix, string $regex = '', ?callable $progress = null ): int {
		$full_prefix = $this->bucket->get_full_path( $prefix );

		try {
			$deleted = $this->client->deleteMatchingObjects(
				$this->bucket->get_name(),
				$full_prefix,
				$regex,
				[
					'before' => $progress,
				]
			);

			return $deleted;
		} catch ( S3Exception $e ) {
			throw new \RuntimeException( $e->getMessage(), (int) $e->getCode(), $e );
		}
	}
}
