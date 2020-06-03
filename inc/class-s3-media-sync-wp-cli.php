<?php

use \WP_CLI\Utils;

class S3_Media_Sync_WP_CLI_Command extends WPCOM_VIP_CLI_Command {

	/**
	 * Upload all validated media to S3
	 *
	 * @subcommand upload-all
	*/
	public function upload_all( $args, $assoc_args ) {
		global $wpdb;

		// Get the source and destination and initialize some concurrency variables
		$from    = wp_get_upload_dir();
		$to      = S3_Media_Sync::init()->get_s3_bucket_url();
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
			$this->stop_the_insanity();
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
	*/
	public function rm( $args, $args_assoc ) {
		$s3     = S3_Media_Sync::init()->s3();
		$bucket = S3_Media_Sync::init()->get_s3_bucket();
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
}

WP_CLI::add_command( 's3-media', 'S3_Media_Sync_WP_CLI_Command' );
