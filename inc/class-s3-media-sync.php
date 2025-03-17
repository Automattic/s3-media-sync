<?php

class S3_Media_Sync {
	private $settings;
	private $s3;
	private $settings_handler;

	public function __construct( S3_Media_Sync_Settings $settings_handler ) {
		$this->settings_handler = $settings_handler;
		$this->settings         = $this->settings_handler->get_settings();
	}

	/**
	 * Get the settings handler instance
	 *
	 * @return S3_Media_Sync_Settings
	 */
	public function get_settings_handler() {
		return $this->settings_handler;
	}

	public function get_s3_bucket() {
		return $this->settings['bucket'];
	}

	public function get_s3_bucket_url() {
		return 's3://' . $this->settings['bucket'];
	}

	/**
	 * Setup for the plugin
	 */
	public function setup() {
		// Load the plugin text domain for translation
		load_plugin_textdomain( 's3-media-sync', false, basename( dirname( __FILE__ ) ) . '/languages/' );

		// Initialize settings
		$this->settings_handler->init();

		// Only proceed with stream wrapper and hooks if we have all required settings
		if ( $this->settings_handler->has_required_settings() && isset($this->settings['region']) && !empty($this->settings['region']) ) {
			// Register and configure the stream wrapper
			$this->register_stream_wrapper();

			// Perform on-the-fly media syncs by hooking into these actions
			add_filter( 'wp_handle_upload', [ $this, 'add_attachment_to_s3' ], 10, 2 );
			add_action( 'delete_attachment', [ $this, 'delete_attachment_from_s3' ], 10, 1 );
			add_filter( 'wp_save_image_editor_file', [ $this, 'add_updated_attachment_to_s3' ], 10, 5 );
		}
	}

	/**
	 * Trigger an upload to S3 to keep the media backups in sync
	 * 
	 * @throws \Aws\S3\Exception\S3Exception If there is an error uploading to S3
	 */
	public function add_attachment_to_s3( $upload, $context ) {
		// Grab the source and destination paths
		$source_path      = $upload['file'];
		$destination_path = $this->get_s3_bucket_url() . wp_parse_url( $upload['url'] )['path'];

		try {
			// Copy the attachment over to S3
			if (!copy( $source_path, $destination_path )) {
				throw new \RuntimeException('Failed to copy file to S3');
			}
		} catch (\Exception $e) {
			// Re-throw S3 exceptions directly
			if ($e instanceof \Aws\S3\Exception\S3Exception) {
				throw $e;
			}
			// For stream wrapper errors, try to extract the AWS error code
			if ($e instanceof \RuntimeException && preg_match('/\[([A-Za-z]+)\]/', $e->getMessage(), $matches)) {
				throw new \Aws\S3\Exception\S3Exception(
					$e->getMessage(),
					new \Aws\Command('PutObject'),
					['code' => $matches[1], 'message' => $e->getMessage()]
				);
			}
			// Wrap other exceptions in an S3Exception
			throw new \Aws\S3\Exception\S3Exception(
				$e->getMessage(),
				new \Aws\Command('PutObject'),
				['code' => 'InternalError', 'message' => $e->getMessage()]
			);
		}

		return $upload;
	}

	/**
	 * Trigger a delete in S3 to keep the media backups in sync
	 */
	public function delete_attachment_from_s3( $post_id ) {
		// Grab the path for the attachment -- this is the S3 key
		$source = wp_get_upload_dir()['baseurl'];
		$bucket = $this->get_s3_bucket();
		$path	= 'wp-content/uploads' . str_replace( $source, '', wp_get_attachment_url( $post_id ) );

		// Grab the bucket prefix (if there is one)
		if ( strpos( $bucket, '/' ) ) {
			$prefix = str_replace( strtok( $bucket, '/' ) . '/', '', $bucket );
			$path   = trailingslashit( $prefix ) . $path;
			$bucket = strtok( $bucket, '/' );
		}

		// Delete the matching attachment
		$this->s3()->deleteMatchingObjects( $bucket, $path );
	}

	/**
	 * When an image is about to be saved from the image editor, save the image first, and then try
	 * and copy it to S3.
	 *
	 * This filter is documented here https://developer.wordpress.org/reference/hooks/wp_save_image_editor_file/
	 */
	public function add_updated_attachment_to_s3( $override, $filename, $image, $mime_type, $post_ID ) {
		// Go ahead and save the image
		$override = $image->save( $filename, $mime_type );

		// If there wasn't an error while saving the image, copy to S3.
		if ( ! is_wp_error( $override ) ) {
			// The image saved, try and send it to S3.
			$source_path      = $filename;
			$destination_path = trailingslashit( $this->get_s3_bucket_url() ) . str_replace( 'vip://', '', $source_path );
			copy( $source_path, $destination_path );
		}

		return $override;
	}

	/**
	 * Props S3 Uploads and HM: https://github.com/humanmade/S3-Uploads/
	 *
	 * Register the stream wrapper for s3
	 */
	public function register_stream_wrapper() {
		// Only proceed to register the stream wrapper if all required fields are set
		if ( ! $this->settings_handler->has_required_settings() ) {
			return;
		}

		// Ensure we have all required settings for S3 client
		if ( ! isset($this->settings['region']) || empty($this->settings['region']) ) {
			return;
		}

		// Register and configure stream wrapper
		S3_Media_Sync_Stream_Wrapper::register( $this->s3() );
		$objectAcl = isset( $this->settings['object_acl'] ) ? sanitize_text_field( $this->settings['object_acl'] ) : 'public-read';
		stream_context_set_option( stream_context_get_default(), 's3', 'ACL', $objectAcl );
		stream_context_set_option( stream_context_get_default(), 's3', 'seekable', true );
	}

	/**
	 * Props S3 Uploads and HM: https://github.com/humanmade/S3-Uploads/
	 *
	 * @return Aws\S3\S3Client
	 */
	public function s3() {
		if ( ! empty( $this->s3 ) ) {
			return $this->s3;
		}

		$params = array( 'version' => 'latest' );

		if ( isset($this->settings['key']) && isset($this->settings['secret']) && $this->settings['key'] && $this->settings['secret'] ) {
			$params['credentials']['key']    = $this->settings['key'];
			$params['credentials']['secret'] = $this->settings['secret'];
		}

		if ( isset($this->settings['region']) && $this->settings['region'] ) {
			$params['signature'] = 'v4';
			$params['region']    = $this->settings['region'];
		}

		if ( defined( 'WP_PROXY_HOST' ) && defined( 'WP_PROXY_PORT' ) ) {
			$proxy_auth    = '';
			$proxy_address = WP_PROXY_HOST . ':' . WP_PROXY_PORT;

			if ( defined( 'WP_PROXY_USERNAME' ) && defined( 'WP_PROXY_PASSWORD' ) ) {
				$proxy_auth = WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD . '@';
			}

			$params['request.options']['proxy'] = $proxy_auth . $proxy_address;
		}

		$this->s3 = Aws\S3\S3Client::factory( $params );

		return $this->s3;
	}
}
