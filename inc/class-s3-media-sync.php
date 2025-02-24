<?php

class S3_Media_Sync {
	private $settings;
	private $settings_handler;

	/**
	 * Constructor.
	 *
	 * @param S3_Media_Sync_Settings $settings_handler Settings handler instance.
	 */
	public function __construct( S3_Media_Sync_Settings $settings_handler ) {
		$this->settings_handler = $settings_handler;
		$this->settings        = $this->settings_handler->get_settings();
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
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->settings);

			// Perform on-the-fly media syncs by hooking into these actions
			add_filter( 'wp_handle_upload', [ $this, 'add_attachment_to_s3' ], 10, 2 );
			add_action( 'delete_attachment', [ $this, 'delete_attachment_from_s3' ], 10, 1 );
			add_filter( 'wp_save_image_editor_file', [ $this, 'add_updated_attachment_to_s3' ], 10, 5 );
		}
	}

	/**
	 * Get the client factory instance
	 *
	 * @return S3_Media_Sync_Client_Factory
	 */
	private function get_client_factory() {
		// Allow tests to override the factory via global
		if (isset($GLOBALS['s3_media_sync_client_factory'])) {
			return $GLOBALS['s3_media_sync_client_factory'];
		}
		return new S3_Media_Sync_Client_Factory();
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
			// For other stream wrapper errors, try to extract the error code from the message
			if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'InvalidAccessKeyId') !== false) {
				throw new \Aws\S3\Exception\S3Exception(
					$e->getMessage(),
					new \Aws\Command('PutObject'),
					['code' => 'InvalidAccessKeyId', 'message' => $e->getMessage()]
				);
			}
			if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'NoSuchBucket') !== false) {
				throw new \Aws\S3\Exception\S3Exception(
					$e->getMessage(),
					new \Aws\Command('PutObject'),
					['code' => 'NoSuchBucket', 'message' => $e->getMessage()]
				);
			}
			if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'Access Denied') !== false) {
				throw new \Aws\S3\Exception\S3Exception(
					$e->getMessage(),
					new \Aws\Command('PutObject'),
					['code' => 'AccessDenied', 'message' => $e->getMessage()]
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
		$factory = $this->get_client_factory();
		$s3_client = $factory->create($this->settings);
		$s3_client->deleteMatchingObjects( $bucket, $path );
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
}
