<?php

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
		global $wpdb;
		
		// Parse arguments
		$verify_size = isset( $assoc_args['verify-size'] ) ? filter_var( $assoc_args['verify-size'], FILTER_VALIDATE_BOOLEAN ) : true;
		$verify_md5 = isset( $assoc_args['verify-md5'] ) ? filter_var( $assoc_args['verify-md5'], FILTER_VALIDATE_BOOLEAN ) : false;
		$fix = isset( $assoc_args['fix'] ) ? filter_var( $assoc_args['fix'], FILTER_VALIDATE_BOOLEAN ) : false;
		$limit = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : 100;
		$offset = isset( $assoc_args['offset'] ) ? absint( $assoc_args['offset'] ) : 0;
		
		// Get S3 client and bucket info
		$s3 = $this->get_s3_media_sync();
		$s3_client = $s3->get_s3_client();
		$bucket = $s3->get_s3_bucket();
		$bucket_parts = explode( '/', $bucket, 2 );
		$bucket_name = $bucket_parts[0];
		$prefix = isset( $bucket_parts[1] ) ? trailingslashit( $bucket_parts[1] ) : '';
		
		// Get uploads directory info
		$uploads = wp_upload_dir();
		$base_dir = $uploads['basedir'];
		$base_url = $uploads['baseurl'];
		
		// Get a batch of attachments
		$sql = $wpdb->prepare(
			"SELECT ID, post_title FROM wp_posts 
			WHERE post_type = 'attachment' 
			ORDER BY ID 
			LIMIT %d OFFSET %d",
			$limit, $offset
		);
		
		$attachments = $wpdb->get_results( $sql );
		
		if ( empty( $attachments ) ) {
			WP_CLI::warning( 'No attachments found.' );
			return;
		}
		
		// Initialize counters
		$count_total = count( $attachments );
		$count_missing = 0;
		$count_size_mismatch = 0;
		$count_md5_mismatch = 0;
		$count_fixed = 0;
		
		// Set up progress bar
		$progress = \WP_CLI\Utils\make_progress_bar( sprintf( 'Verifying %d attachments', $count_total ), $count_total );
		
		// Track verification details
		$verification_issues = array();
		
		foreach ( $attachments as $attachment ) {
			$attachment_id = $attachment->ID;
			$attachment_url = wp_get_attachment_url( $attachment_id );
			
			if ( empty( $attachment_url ) ) {
				$progress->tick();
				continue;
			}
			
			// Get file path information
			$relative_url_path = str_replace( $base_url, '', $attachment_url );
			$local_file_path = $base_dir . $relative_url_path;
			$s3_key = 'wp-content/uploads' . $relative_url_path;
			
			// Skip if local file doesn't exist
			if ( ! file_exists( $local_file_path ) ) {
				$progress->tick();
				continue;
			}
			
			// Get local file information
			$local_size = filesize( $local_file_path );
			$local_md5 = $verify_md5 ? md5_file( $local_file_path ) : '';
			
			// Check if file exists in S3
			$s3_exists = false;
			$s3_size = 0;
			$s3_etag = '';
			$issue_type = '';
			$is_fixed = false;
			
			try {
				$s3_object = $s3_client->headObject([
					'Bucket' => $bucket_name,
					'Key' => $prefix . $s3_key,
				]);
				
				$s3_exists = true;
				$s3_size = isset( $s3_object['ContentLength'] ) ? $s3_object['ContentLength'] : 0;
				$s3_etag = isset( $s3_object['ETag'] ) ? trim( $s3_object['ETag'], '"' ) : '';
				
				// Check size if requested
				if ( $verify_size && $s3_size != $local_size ) {
					$issue_type = 'Size mismatch';
					$count_size_mismatch++;
					
					if ( $fix ) {
						// Upload the corrected file
						try {
							$s3_client->putObject([
								'Bucket' => $bucket_name,
								'Key' => $prefix . $s3_key,
								'SourceFile' => $local_file_path,
								'ACL' => 'public-read',
							]);
							$count_fixed++;
							$is_fixed = true;
						} catch ( \Exception $upload_e ) {
							// Failed to fix
						}
					}
				}
				// Check MD5 if requested and there's no size mismatch
				elseif ( $verify_md5 && empty( $issue_type ) ) {
					// S3 ETags are MD5 hashes for non-multipart uploads
					if ( $s3_etag !== $local_md5 ) {
						$issue_type = 'MD5 mismatch';
						$count_md5_mismatch++;
						
						if ( $fix ) {
							// Upload the corrected file
							try {
								$s3_client->putObject([
									'Bucket' => $bucket_name,
									'Key' => $prefix . $s3_key,
									'SourceFile' => $local_file_path,
									'ACL' => 'public-read',
								]);
								$count_fixed++;
								$is_fixed = true;
							} catch ( \Exception $upload_e ) {
								// Failed to fix
							}
						}
					}
				}
				
			} catch ( \Exception $e ) {
				// File doesn't exist on S3
				$s3_exists = false;
				$issue_type = 'Missing on S3';
				$count_missing++;
				
				if ( $fix ) {
					// Upload the missing file
					try {
						$s3_client->putObject([
							'Bucket' => $bucket_name,
							'Key' => $prefix . $s3_key,
							'SourceFile' => $local_file_path,
							'ACL' => 'public-read',
						]);
						$count_fixed++;
						$is_fixed = true;
					} catch ( \Exception $upload_e ) {
						// Failed to fix
					}
				}
			}
			
			// Record verification issues
			if ( ! empty( $issue_type ) ) {
				$verification_issues[] = array(
					'ID' => $attachment_id,
					'Title' => $attachment->post_title,
					'Issue' => $issue_type,
					'Local Size' => size_format( $local_size, 2 ),
					'S3 Size' => $s3_exists ? size_format( $s3_size, 2 ) : 'N/A',
					'Fixed' => $is_fixed ? 'Yes' : 'No',
				);
			}
			
			$progress->tick();
		}
		
		$progress->finish();
		
		// Report results
		WP_CLI::line( '' );
		WP_CLI::line( 'Verification Results:' );
		WP_CLI::line( sprintf( 'Total attachments: %d', $count_total ) );
		WP_CLI::line( sprintf( 'Missing on S3: %d', $count_missing ) );
		WP_CLI::line( sprintf( 'Size mismatches: %d', $count_size_mismatch ) );
		
		if ( $verify_md5 ) {
			WP_CLI::line( sprintf( 'MD5 mismatches: %d', $count_md5_mismatch ) );
		}
		
		if ( $fix ) {
			WP_CLI::line( sprintf( 'Issues fixed: %d', $count_fixed ) );
		}
		
		// Display issues in a table
		if ( ! empty( $verification_issues ) ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Issues found:' );
			
			$table_fields = array( 'ID', 'Title', 'Issue', 'Local Size', 'S3 Size' );
			
			if ( $fix ) {
				$table_fields[] = 'Fixed';
			}
			
			WP_CLI\Utils\format_items( 'table', $verification_issues, $table_fields );
		} else {
			WP_CLI::success( 'All verified files are in sync with S3.' );
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
	 * @return S3_Media_Sync
	 */
	private function get_s3_media_sync() {
		$settings_handler = new S3_Media_Sync_Settings();
		$settings = $settings_handler->get_settings();
		$tester = new S3_Media_Sync_Tester($settings);
		$s3_media_sync = new S3_Media_Sync($settings_handler);
		$s3_media_sync->setup();
		return $s3_media_sync;
	}

}
