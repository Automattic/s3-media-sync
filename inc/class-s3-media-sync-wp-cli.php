<?php

use S3_Media_Sync\Services\S3_Repository;
use S3_Media_Sync\Services\Verify_Service;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Verify_Result;
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
	 * @synopsis <attachment_id>
	 *
	 * ## OPTIONS
	 *
	 * <attachment_id>
	 * : The ID of the attachment to upload to S3.
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload an attachment with ID 123 to S3.
	 *     $ wp s3-media upload 123
	 */
	public function upload( $args, $assoc_args ) {
		// Get the source and destination and initialize some concurrency variables
		$from	= wp_get_upload_dir();
		$to	= $this->get_s3_media_sync()->get_s3_bucket_url();
		
		$attachment_id = absint( $args[0] );
	
		if ( $attachment_id === 0 ) {
			WP_CLI::error( 'Invalid attachment ID.' );
		}
	
		$url = wp_get_attachment_url( $attachment_id );
	
		if ( false === $url || '' === $url ) {
			WP_CLI::error( 'Failed to retrieve attachment URL for ID: ' . $attachment_id );
		}
	
		// By switching the URLs from http:// to https:// we save a request, since it will be redirected to the SSL url
		if ( is_ssl() ) {
			$url = str_replace( 'http://', 'https://', $url );
		}
	
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
	
		// Check for errors before setting options
		if ( curl_errno( $ch ) ) {
			WP_CLI::error( 'cURL error for attachment ID ' . $attachment_id . ': ' . curl_error( $ch ) );
		}
	
		$response = curl_exec( $ch );
		
		// Check for errors after executing cURL request
		if ( curl_errno( $ch ) ) {
			WP_CLI::error( 'cURL error for attachment ID ' . $attachment_id . ': ' . curl_error( $ch ) );
		}
	
		$response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
	
		if ( 200 === $response_code ) {
			// Process the response and upload the attachment to S3
			$path = str_replace( $from['baseurl'], '', $url );

			// Check if the file exists before copying it over
			if ( ! is_file( trailingslashit( $to ) . 'wp-content/uploads' . $path ) ) {
				copy( $from['basedir'] . $path, trailingslashit( $to ) . 'wp-content/uploads' . $path );
			}
			WP_CLI::success( 'Attachment ID ' . $attachment_id . ' successfully uploaded to S3.' );
		} else {
			WP_CLI::error( 'Failed to fetch attachment from URL for attachment ID ' . $attachment_id . ': ' . $url );
		}
	}
	
	/**
	 * Upload all validated media to S3
	 *
	 * @subcommand upload-all
	 *
	 * ## OPTIONS
	 *
	 * [--threads=<number>]
	 * : The number of concurrent threads to use for uploading. Defaults to 10.
	 * ---
	 * default: 10
	 * options:
	 *   - 1-10
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload all media to S3 using the default number of threads.
	 *     $ wp s3-media upload-all
	 *
	 *     # Upload all media to S3 using 5 threads.
	 *     $ wp s3-media upload-all --threads=5
	 */
	public function upload_all( $args, $assoc_args ) {
		global $wpdb;

		// Get the source and destination and initialize some concurrency variables
		$from    = wp_get_upload_dir();
		$to      = $this->get_s3_media_sync()->get_s3_bucket_url();
		$offset  = 0;
		$threads = 10;
		$limit   = 500;

		// Let's see how many attachments we'll be working through
		$count_sql        = 'SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE post_type = "attachment"';
		$attachment_count = $wpdb->get_row( $count_sql, ARRAY_N )[0];
		$progress         = \WP_CLI\Utils\make_progress_bar( 'Uploading ' . number_format( $attachment_count ) . ' attachments', $attachment_count );

		do {
			// Grab a chunk of attachments to work through
			$sql         = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "attachment" LIMIT %d,%d', $offset, $limit );
			$attachments = $wpdb->get_results( $sql );

			// Break the attachments into groups of maxiumum 10 elements
			$attachments_arrays = array_chunk( $attachments, $threads );
			$mh                 = curl_multi_init();

			// Loop through each block of 10 attachments
			foreach ( $attachments_arrays as $attachments_array ) {
				$ch    = array();
				$index = 0;

				foreach ( $attachments_array as $attachment ) {
					$url = wp_get_attachment_url( $attachment->ID );

					// By switching the URLs from http:// to https:// we save a request, since it will be redirected to the SSL url
					if ( is_ssl() ) {
						$url = str_replace( 'http://', 'https://', $url );
					}

					$ch[ $index ] = curl_init();
					curl_setopt( $ch[ $index ], CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch[ $index ], CURLOPT_URL, $url );
					curl_setopt( $ch[ $index ], CURLOPT_FOLLOWLOCATION, true );
					curl_setopt( $ch[ $index ], CURLOPT_NOBODY, true );
					curl_multi_add_handle( $mh, $ch[ $index ] );
					$index++;
				}

				// Exec the cURL requests
				$curl_active = null;

				do {
					$mrc = curl_multi_exec( $mh, $curl_active );
				} while ( $curl_active > 0 );

				// Process the responses
				foreach ( $ch as $index => $handle ) {
					$response_code = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
					$url           = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );

					if ( 200 === $response_code ) {
						$path = str_replace( $from['baseurl'], '', $url );

						// Check if file exists before copying it over
						if ( ! is_file( trailingslashit( $to ) . 'wp-content/uploads' . $path ) ) {
							copy( $from['basedir'] . $path, trailingslashit( $to ) . 'wp-content/uploads' . $path );
						}
					}

					curl_multi_remove_handle( $mh, $handle );
					$progress->tick();
				}
			}
			// Pause and clear caches to free up memory
			$this->reset_local_object_cache();
			$this->reset_db_query_log();
			sleep( 1 );
			$offset += $limit;
		} while ( count( $attachments ) );

		$progress->finish();

		WP_CLI::success( sprintf( 'Successfully uploaded media to %s', $to ) );
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
					function() {
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
		$s3 = $this->get_s3_media_sync();
		$settings_handler = $s3->get_settings_handler();
		$settings = $settings_handler->get_settings();
		
		// Check if required settings are available
		if ( ! $settings_handler->has_required_settings() ) {
			WP_CLI::error( 'S3 Media Sync is not properly configured. Please set up your AWS credentials in the WordPress admin.' );
			return;
		}
		
		// Check S3 connection
		try {
			$bucket = $s3->get_s3_bucket();
			$bucket_parts = explode( '/', $bucket, 2 );
			$bucket_name = $bucket_parts[0];
			
			// Get the S3 client
			$s3_client = $s3->get_s3_client();
			
			// Check bucket accessibility by performing a head bucket request
			$s3_client->headBucket( array(
				'Bucket' => $bucket_name
			) );
			
			// Display configuration information
			WP_CLI::success( 'Successfully connected to S3' );
			
			// Create a formatted table of settings
			$settings_data = array();
			$settings_data[] = array(
				'Setting' => 'Bucket',
				'Value' => $bucket
			);
			$settings_data[] = array(
				'Setting' => 'Region',
				'Value' => $settings['region']
			);
			$settings_data[] = array(
				'Setting' => 'Use ACLs',
				'Value' => isset( $settings['use_acl'] ) && $settings['use_acl'] ? 'Yes' : 'No'
			);
			$settings_data[] = array(
				'Setting' => 'Object ACL',
				'Value' => isset( $settings['object_acl'] ) ? $settings['object_acl'] : 'Not set'
			);
			$settings_data[] = array(
				'Setting' => 'Sync Thumbnails',
				'Value' => isset( $settings['sync_thumbnails'] ) && $settings['sync_thumbnails'] !== false ? 'Yes' : 'No'
			);
			
			// Display table
			WP_CLI\Utils\format_items( 'table', $settings_data, array( 'Setting', 'Value' ) );
			
			// List IAM user/role details if possible
			try {
				$sts_client = new \Aws\Sts\StsClient([
					'region' => $settings['region'],
					'version' => 'latest',
					'credentials' => [
						'key' => $settings['key'],
						'secret' => $settings['secret']
					]
				]);
				$identity = $sts_client->getCallerIdentity();
				
				WP_CLI::line( '' ); // Empty line for spacing
				WP_CLI::line( 'AWS Account Information:' );
				WP_CLI::line( '- Account ID: ' . $identity['Account'] );
				WP_CLI::line( '- IAM User/Role: ' . $identity['Arn'] );
			} catch ( \Exception $e ) {
				WP_CLI::warning( 'Unable to retrieve AWS identity information: ' . $e->getMessage() );
			}
			
		} catch ( \Exception $e ) {
			WP_CLI::error( sprintf( 'Failed to connect to S3: %s', $e->getMessage() ) );
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
		$options = [
			'verify_size' => isset( $assoc_args['verify-size'] ) ? filter_var( $assoc_args['verify-size'], FILTER_VALIDATE_BOOLEAN ) : true,
			'verify_md5'  => isset( $assoc_args['verify-md5'] ) ? filter_var( $assoc_args['verify-md5'], FILTER_VALIDATE_BOOLEAN ) : false,
		];
		$fix    = isset( $assoc_args['fix'] ) ? filter_var( $assoc_args['fix'], FILTER_VALIDATE_BOOLEAN ) : false;
		$limit  = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 100;
		$offset = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;

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
			$table_fields = [ 'ID', 'Title', 'Issue', 'Local Size', 'S3 Size' ];

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
		global $wpdb;
		
		$path = isset( $assoc_args['path'] ) ? $assoc_args['path'] : 'wp-content/uploads';
		$delete = isset( $assoc_args['delete'] );
		
		// Get the S3 client and bucket info
		$s3 = $this->get_s3_media_sync();
		$s3_client = $s3->get_s3_client();
		$bucket = $s3->get_s3_bucket();
		$bucket_parts = explode( '/', $bucket, 2 );
		$bucket_name = $bucket_parts[0];
		$prefix = isset( $bucket_parts[1] ) ? trailingslashit( $bucket_parts[1] ) : '';
		
		// Full prefix for S3 listing
		$full_prefix = trailingslashit( $prefix . $path );
		
		WP_CLI::line( sprintf( 'Scanning S3 bucket "%s" with prefix "%s"', $bucket_name, $full_prefix ) );
		
		// Get list of files from S3
		$s3_files = array();
		$marker = '';
		$continue = true;
		$total_files = 0;
		
		WP_CLI::line( 'Retrieving file list from S3...' );
		
		while ( $continue && $total_files < 1000 ) {
			try {
				$params = array(
					'Bucket' => $bucket_name,
					'Prefix' => $full_prefix,
					'MaxKeys' => min( 1000, 1000 - $total_files ),
				);
				
				if ( ! empty( $marker ) ) {
					$params['Marker'] = $marker;
				}
				
				$objects = $s3_client->listObjects( $params );
				
				if ( empty( $objects['Contents'] ) ) {
					$continue = false;
					continue;
				}
				
				foreach ( $objects['Contents'] as $object ) {
					$key = $object['Key'];
					
					// Skip directory objects (keys ending with /)
					if ( substr( $key, -1 ) === '/' ) {
						continue;
					}
					
					// Remove the bucket prefix to get the relative path
					$relative_key = substr( $key, strlen( $prefix ) );
					$s3_files[ $relative_key ] = array(
						'key' => $key,
						'size' => $object['Size'],
						'last_modified' => $object['LastModified'],
					);
					
					$total_files++;
				}
				
				// Set marker for next batch
				if ( $objects['IsTruncated'] && isset( $objects['NextMarker'] ) ) {
					$marker = $objects['NextMarker'];
				} elseif ( $objects['IsTruncated'] && ! empty( $objects['Contents'] ) ) {
					$last = end( $objects['Contents'] );
					$marker = $last['Key'];
				} else {
					$continue = false;
				}
				
			} catch ( \Exception $e ) {
				WP_CLI::error( sprintf( 'Failed to list objects from S3: %s', $e->getMessage() ) );
				return;
			}
		}
		
		if ( empty( $s3_files ) ) {
			WP_CLI::success( 'No files found in the specified S3 path.' );
			return;
		}
		
		WP_CLI::line( sprintf( 'Found %d files in S3', count( $s3_files ) ) );
		
		// Get uploads directory info
		$uploads = wp_upload_dir();
		$uploads_url = $uploads['baseurl'];
		$uploads_path = 'wp-content/uploads';
		
		// Now check which files exist in WordPress
		$orphaned_files = array();
		
		WP_CLI::line( '' );
		WP_CLI::line( 'Checking WordPress attachments...' );
		
		// Initialize progress bar for total files
		$progress = \WP_CLI\Utils\make_progress_bar( 'Progress', count( $s3_files ) );
		$total_processed = 0;
		
		// Batch process the files to avoid memory issues
		$batch_size = 100;
		$batches = array_chunk( array_keys( $s3_files ), $batch_size, true );
		
		foreach ( $batches as $batch ) {
			// Filter out files that wouldn't be in WordPress uploads
			$wp_check_files = array();
			
			foreach ( $batch as $relative_key ) {
				// Skip if not in uploads directory
				if ( strpos( $relative_key, $uploads_path ) !== 0 ) {
					continue;
				}
				
				// Get the file path relative to uploads dir
				$file_path = substr( $relative_key, strlen( $uploads_path ) );
				$file_path = ltrim( $file_path, '/' );
				
				// Normalize the path - replace spaces with hyphens and clean special characters
				$normalized_path = preg_replace('/\s+/', '-', $file_path);
				$normalized_path = sanitize_file_name($normalized_path);
				
				// Skip certain file patterns like temporary files
				if ( preg_match( '/-e\d+\.|-\d+x\d+\./', $normalized_path ) ) {
					// These are typically WordPress-generated temporary files
					// Consider them orphaned by default
					$orphaned_files[ $relative_key ] = $s3_files[ $relative_key ];
					continue;
				}
				
				$wp_check_files[ $relative_key ] = $normalized_path;
			}
			
			// Skip to next batch if no files to check
			if ( empty( $wp_check_files ) ) {
				foreach ( $batch as $relative_key ) {
					$progress->tick();
				}
				continue;
			}
			
			// First try exact matches
			$placeholders = implode( ',', array_fill( 0, count( $wp_check_files ), '%s' ) );
			$query = $wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} 
				WHERE meta_key = '_wp_attached_file'",
				array()
			);
			
			$results = $wpdb->get_col( $query );
			$found_files = array();
			
			// Normalize the results
			foreach ( $results as $file ) {
				// WordPress stores paths without wp-content/uploads prefix
				// Store both with and without prefix for comparison
				$found_files[ $file ] = true;
				$found_files[ $uploads_path . '/' . ltrim( $file, '/' ) ] = true;
				
				// Also store normalized versions
				$normalized = preg_replace('/\s+/', '-', $file);
				$normalized = sanitize_file_name($normalized);
				$found_files[ $normalized ] = true;
				$found_files[ $uploads_path . '/' . ltrim( $normalized, '/' ) ] = true;
			}
			
			// Check which files are orphaned
			foreach ( $wp_check_files as $relative_key => $file_path ) {
				$check_path = substr( $relative_key, strlen( $uploads_path . '/' ) );
				if ( ! isset( $found_files[ $check_path ] ) && ! isset( $found_files[ $relative_key ] ) ) {
					$orphaned_files[ $relative_key ] = $s3_files[ $relative_key ];
				}
				$progress->tick();
				$total_processed++;
			}
			
			// Update progress for any skipped files in this batch
			foreach ( $batch as $relative_key ) {
				if ( ! isset( $wp_check_files[ $relative_key ] ) ) {
					$progress->tick();
					$total_processed++;
				}
			}
		}
		
		$progress->finish();
		
		// Display results
		WP_CLI::line( '' );
		WP_CLI::line( sprintf( 'Found %d orphaned files in S3', count( $orphaned_files ) ) );
		
		if ( empty( $orphaned_files ) ) {
			WP_CLI::success( 'No orphaned files to clean up.' );
			return;
		}
		
		// Group by year/month for better reporting
		$grouped_files = array();
		$total_size = 0;
		
		foreach ( $orphaned_files as $relative_key => $file_info ) {
			// Extract year/month from path
			$matches = array();
			if ( preg_match( '|/(\d{4})/(\d{2})/|', $relative_key, $matches ) ) {
				$group = $matches[1] . '/' . $matches[2];
			} else {
				$group = 'other';
			}
			
			if ( ! isset( $grouped_files[ $group ] ) ) {
				$grouped_files[ $group ] = array(
					'count' => 0,
					'size' => 0,
					'files' => array(),
				);
			}
			
			$grouped_files[ $group ]['count']++;
			$grouped_files[ $group ]['size'] += $file_info['size'];
			$grouped_files[ $group ]['files'][] = $relative_key;
			$total_size += $file_info['size'];
		}
		
		// Display summary by group
		$table_data = array();
		foreach ( $grouped_files as $group => $info ) {
			$table_data[] = array(
				'Group' => $group,
				'Count' => $info['count'],
				'Size' => size_format( $info['size'], 2 ),
			);
		}
		
		// Add total row
		$table_data[] = array(
			'Group' => 'TOTAL',
			'Count' => count( $orphaned_files ),
			'Size' => size_format( $total_size, 2 ),
		);
		
		WP_CLI\Utils\format_items( 'table', $table_data, array( 'Group', 'Count', 'Size' ) );
		
		// List some example files
		$examples = array_slice( array_keys( $orphaned_files ), 0, 10 );
		WP_CLI::line( '' );
		WP_CLI::line( 'Example orphaned files:' );
		foreach ( $examples as $example ) {
			WP_CLI::line( ' - ' . $example );
		}
		
		// If there are more files than shown in the examples
		if ( count( $orphaned_files ) > count( $examples ) ) {
			WP_CLI::line( sprintf( '... and %d more', count( $orphaned_files ) - count( $examples ) ) );
		}
		
		// Delete orphaned files if requested
		if ( $delete ) {
			WP_CLI::line( '' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::line( 'DANGER: You are about to delete files!' );
			WP_CLI::line( '----------------------------------------' );
			WP_CLI::confirm( sprintf( 'Are you sure you want to delete %d orphaned files from S3?', count( $orphaned_files ) ) );
			
			$progress = \WP_CLI\Utils\make_progress_bar( 'Deleting orphaned files', count( $orphaned_files ) );
			$delete_count = 0;
			$failed_count = 0;
			
			// Process in batches of 1000 (maximum for S3 deleteObjects)
			$delete_batches = array_chunk( array_keys( $orphaned_files ), 1000 );
			
			foreach ( $delete_batches as $batch ) {
				$objects = array();
				
				foreach ( $batch as $relative_key ) {
					$objects[] = array(
						'Key' => $orphaned_files[ $relative_key ]['key'],
					);
				}
				
				try {
					$result = $s3_client->deleteObjects([
						'Bucket' => $bucket_name,
						'Delete' => [
							'Objects' => $objects,
							'Quiet' => true,
						],
					]);
					
					$delete_count += count( $objects );
					
					if ( ! empty( $result['Errors'] ) ) {
						$failed_count += count( $result['Errors'] );
						foreach ( $result['Errors'] as $error ) {
							WP_CLI::warning( sprintf( 'Failed to delete %s: %s', $error['Key'], $error['Message'] ) );
						}
					}
					
				} catch ( \Exception $e ) {
					WP_CLI::error( sprintf( 'Error deleting objects: %s', $e->getMessage() ) );
					return;
				}
				
				// Update progress
				foreach ( $batch as $relative_key ) {
					$progress->tick();
				}
			}
			
			$progress->finish();
			
			WP_CLI::success( sprintf( 'Successfully deleted %d orphaned files from S3', $delete_count ) );
			
			if ( $failed_count > 0 ) {
				WP_CLI::warning( sprintf( 'Failed to delete %d files', $failed_count ) );
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

		$properties = [
			'group_ops',
			'memcache_debug',
			'cache',
		];

		foreach ( $properties as $property ) {
			if ( property_exists( $wp_object_cache, $property ) ) {
				$wp_object_cache->$property = [];
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
		$s3              = $this->get_s3_media_sync();
		$settings        = $s3->get_settings_handler()->get_settings();
		$bucket          = S3_Bucket::from_settings( $settings );
		$repository      = new S3_Repository( $s3->get_s3_client(), $bucket );

		return new Verify_Service( $repository );
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

		$params = [
			'Bucket'     => $bucket_name,
			'Key'        => $key,
			'SourceFile' => $source_file,
		];

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
