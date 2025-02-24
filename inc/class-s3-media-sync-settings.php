<?php

class S3_Media_Sync_Settings {
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	public function __construct() {
		$this->settings = get_option( 's3_media_sync_settings', [] );
	}

	/**
	 * Initialize settings functionality
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_menu_settings' ] );
		add_action( 'admin_init', [ $this, 'settings_screen_init' ] );
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
	public function settings_validation( $input ) {
		// Only proceed to validate the bucket if all necessary settings are set
		if ( ! $this->has_required_settings() ) {
			return $input;
		}

		// This check will test the API keys provided
		$factory = new S3_Media_Sync_Client_Factory();
		try {
			$s3_client = $factory->create( $this->settings );
			if ( false === $s3_client->doesBucketExist( $this->settings['bucket'] ) ) {
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-settings-error',
					__( 'The credentials provided are incorrect. The AWS bucket cannot be found.', 's3-media-sync' )
				);
			}
		} catch (\Exception $e) {
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
			<h2><?php _e( 'S3 Media Sync', 's3-media-sync' ); ?></h2>
				<form action='options.php' method='post'>
					<?php settings_fields( 's3_media_sync_settings_page' ); ?>
					<?php do_settings_sections( 's3_media_sync_settings_page' ); ?>
					<?php submit_button(); ?>
				</form>
		</div>
		<?php
	}

	/**
	 * Initialize the settings screen
	 */
	public function settings_screen_init() {
		// Register the settings page
		register_setting( 
			's3_media_sync_settings_page', 
			's3_media_sync_settings', 
			[ $this, 'settings_validation' ]
		);

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
	 * Check if all required s3 bucket settings are set.
	 *
	 * @return bool
	 */
	public function has_required_settings() {
		$required_keys = [ 'bucket', 'key', 'secret', 'region' ];

		foreach ( $required_keys as $key ) {
			if ( empty( $this->settings[ $key ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Update settings
	 *
	 * @param array $settings New settings to save
	 */
	public function update_settings( array $settings ) {
		$this->settings = $settings;
		update_option( 's3_media_sync_settings', $settings );
	}

	/**
	 * Get a specific setting value
	 *
	 * @param string $key Setting key to retrieve
	 * @param mixed $default Default value if setting doesn't exist
	 * @return mixed
	 */
	public function get_setting( string $key, $default = null ) {
		return $this->settings[$key] ?? $default;
	}

	/**
	 * Update a specific setting
	 *
	 * @param string $key Setting key to update
	 * @param mixed $value New value
	 */
	public function update_setting( string $key, $value ) {
		$this->settings[$key] = $value;
		update_option( 's3_media_sync_settings', $this->settings );
	}
} 
