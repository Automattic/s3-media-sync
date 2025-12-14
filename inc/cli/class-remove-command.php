<?php
/**
 * Remove Command
 *
 * WP-CLI command for removing files from S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\S3_Repository_Interface;
use WP_CLI;

/**
 * Remove files from S3.
 *
 * Props S3 Uploads and HM: https://github.com/humanmade/S3-Uploads/
 *
 * @since 2.1.0
 */
class Remove_Command {

	/**
	 * The S3 repository.
	 *
	 * @var S3_Repository_Interface
	 */
	private S3_Repository_Interface $repository;

	/**
	 * Constructor.
	 *
	 * @param S3_Repository_Interface $repository The S3 repository.
	 */
	public function __construct( S3_Repository_Interface $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Remove files from S3.
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
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$path  = $args[0] ?? '';
		$regex = $assoc_args['regex'] ?? '';

		if ( empty( $path ) ) {
			WP_CLI::error( 'Path is required.' );
		}

		// Normalize the path.
		$prefix = ltrim( $path, '/' );

		// If path doesn't contain a file extension, treat it as a directory.
		if ( strpos( $path, '.' ) === false ) {
			$prefix = trailingslashit( $prefix );
		}

		WP_CLI::line( sprintf( 'Deleting files matching prefix: %s', $prefix ) );

		if ( ! empty( $regex ) ) {
			WP_CLI::line( sprintf( 'With regex filter: %s', $regex ) );
		}

		try {
			$deleted = $this->repository->delete_by_prefix(
				$prefix,
				$regex,
				function () {
					WP_CLI::line( 'Deleting file...' );
				}
			);

			WP_CLI::success( sprintf( 'Successfully deleted %d file(s) from %s', $deleted, $prefix ) );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}
}
