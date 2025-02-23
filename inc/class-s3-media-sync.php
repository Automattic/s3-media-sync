<?php

class S3_Media_Sync {

	private static $instance;
	private $settings;
	private $s3;

	/**
	 *
	 * @return S3_Media_Sync
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new S3_Media_Sync();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->settings = get_option( 's3_media_sync_settings' );
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

		// Register and configure the stream wrapper
		$this->register_stream_wrapper();

		// Register and render the settings screen
		add_action( 'admin_menu', [ $this, 'register_menu_settings' ] );
		add_action( 'admin_init', [ $this, 'settings_screeen_init' ] );

		if ( $this->has_required_settings() ) {
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
		if ( ! $this->has_required_settings() ) {
			return;
		}

		// Register and configure stream wrapper
		S3_Media_Sync_Stream_Wrapper::register( $this->s3() );
		$objectAcl = isset( $this->settings['object_acl'] ) ? sanitize_text_field( $this->settings['object_acl'] ) : 'public-read';
		stream_context_set_option( stream_context_get_default(), 's3', 'ACL', $objectAcl );
		stream_context_set_option( stream_context_get_default(), 's3', 'seekable', true );
	}

	/**
	 * Register the submenu for updating the settings
	 */
	public function register_menu_settings() {
		add_submenu_page(
			'options-general.php',
			__( 'S3 Media Sync', 's3-media-sync' ),
			__( 'S3 Media Sync', 's3-media-sync' ),
			'manage_options',
			's3_media_sync',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Validate the settings page keys
	 */
	function s3_media_sync_settings_validation( $input ) {
		// Only proceed to validate the bucket if all necessary settings are set
		if ( ! $this->has_required_settings() ) {
			return $input;
		}

		// This check will test the API keys provided
		if ( false === $this->s3()->doesBucketExist( $this->settings['bucket'] ) ) {
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				__( 'The credentials provided are incorrect. The AWS bucket cannot be found.', 's3-media-sync' )
			);
		}

		return $input;
	}

	/**
	 * Render the settings page
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2>S3 Media Sync</h2>
				<form action='options.php' method='post'>
					<?php settings_fields( 's3_media_sync_settings_page' ); ?>
					<?php do_settings_sections( 's3_media_sync_settings_page' ); ?>
					<?php submit_button(); ?>
				</form>
		</div>
		<?php
	}

	public function settings_screeen_init() {
		// Register the settings page
		register_setting( 's3_media_sync_settings_page', 's3_media_sync_settings', [ $this, 's3_media_sync_settings_validation' ] );

		// Add the settings fields
		add_settings_section(
			's3_media_sync_settings',
			__( 'Settings', 's3-media-sync' ),
			null,
			's3_media_sync_settings_page'
		);

		// Setting: S3 Access Key ID
		add_settings_field(
			'key',
			__( 'S3 Access Key ID', 's3-media-sync' ),
			[ $this, 's3_key_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[key]' ]
		);

		// Setting: S3 Secret Access Key
		add_settings_field(
			'secret',
			__( 'S3 Secret Access Key', 's3-media-sync' ),
			[ $this, 's3_secret_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[secret]' ]
		);

		// Setting: S3 Bucket Name
		add_settings_field(
			'bucket',
			__( 'S3 Bucket Name', 's3-media-sync' ),
			[ $this, 's3_bucket_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[bucket]' ]
		);

		// Setting: S3 Region
		add_settings_field(
			'region',
			__( 'S3 Region', 's3-media-sync' ),
			[ $this, 's3_region_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[region]' ]
		);

		// Setting: S3 Object ACL
		add_settings_field(
			'object_acl',
			__( 'S3 Object ACL', 's3-media-sync' ),
			[ $this, 's3_object_acl_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[object_acl]' ]
		);
	}

	// Render the S3 Access Key ID text field
	public function s3_key_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['key'] ) ? $options['key'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[key]" id="s3_media_sync_settings[key]" value="%s">',
			esc_attr( $value )
		);
	}

	// Render the S3 Secret Access Key text field
	public function s3_secret_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['secret'] ) ? $options['secret'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[secret]" id="s3_media_sync_settings[secret]" value="%s">',
			esc_attr( $value )
		);
	}

	// Render the S3 Bucket Name text field
	public function s3_bucket_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['bucket'] ) ? $options['bucket'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[bucket]" id="s3_media_sync_settings[bucket]" value="%s">',
			esc_attr( $value )
		);
	}

	// Render the S3 Region text field
	public function s3_region_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['region'] ) ? $options['region'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[region]" id="s3_media_sync_settings[region]" value="%s">',
			esc_attr( $value )
		);
	}

	// Render the S3 Object ACL dropdown
	public function s3_object_acl_render() {
		$options = get_option('s3_media_sync_settings');
		$value = !empty($options['object_acl']) ? $options['object_acl'] : '';
		?>
		<select name="s3_media_sync_settings[object_acl]" id="s3_media_sync_settings[object_acl]">
			<option<?php selected($value, 'private', true); ?>><?php _e('private', 's3-media-sync'); ?></option>
			<option<?php selected($value, 'public-read', true); ?>><?php _e('public-read', 's3-media-sync'); ?></option>
		</select>
		<?php
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

		if ( $this->settings['key'] && $this->settings['secret'] ) {
			$params['credentials']['key']    = $this->settings['key'];
			$params['credentials']['secret'] = $this->settings['secret'];
		}

		if ( $this->settings['region'] ) {
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

	/**
	 * Check if all required s3 bucket settings are set.
	 *
	 * @return bool
	 */
	private function has_required_settings() {
		$required_keys = [ 'bucket', 'key', 'secret', 'region' ];

		foreach ( $required_keys as $key ) {
			if ( empty( $this->settings[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}
}
