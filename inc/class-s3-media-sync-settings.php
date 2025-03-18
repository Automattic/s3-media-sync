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
		add_action( 'admin_post_s3_media_sync_test_access', [ $this, 'handle_test_access' ] );
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
		$required_keys = [ 'bucket', 'key', 'secret', 'region' ];
		$missing_keys = [];

		// Check for missing required fields
		foreach ( $required_keys as $key ) {
			if ( empty( $input[$key] ) ) {
				$missing_keys[] = $key;
			}
		}

		// If any required fields are missing, add an error and return the old settings
		if ( !empty($missing_keys) ) {
			$missing_fields = implode(', ', array_map(function($key) {
				switch($key) {
					case 'key': return 'Access Key ID';
					case 'secret': return 'Secret Access Key';
					case 'bucket': return 'Bucket Name';
					case 'region': return 'Region';
					default: return $key;
				}
			}, $missing_keys));
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				sprintf(
					__( 'The following required fields are missing: %s', 's3-media-sync' ),
					$missing_fields
				)
			);
			return $this->settings; // Return old settings
		}

		// Normalize use_acl option (convert checkbox to boolean)
		$input['use_acl'] = isset($input['use_acl']) ? (bool)$input['use_acl'] : false;

		// Update the current settings with the new input for validation
		$this->settings = $input;

		// Create the client factory
		$factory = new S3_Media_Sync_Client_Factory();

		try {
			// Attempt to create a client - this will throw an exception if credentials are invalid
			$s3_client = $factory->create($this->settings);

			// If we get here, the client was created successfully
			// Attempt to get AWS account info for debugging
			try {
				$debug_info[] = "- Checking AWS account info";
				$sts_client = new \Aws\Sts\StsClient([
					'region' => $this->settings['region'],
					'version' => 'latest',
					'credentials' => [
						'key' => $this->settings['key'],
						'secret' => $this->settings['secret']
					]
				]);
				$identity = $sts_client->getCallerIdentity();
				$debug_info[] = "- AWS Account: " . $identity['Account'];
				$debug_info[] = "- IAM User/Role ARN: " . $identity['Arn'];
			} catch (\Exception $sts_e) {
				$debug_info[] = "- Unable to get AWS account info: " . $sts_e->getMessage();
			}
			
			// Extract the actual bucket name if there's a path prefix
			$bucket_parts = explode('/', $this->settings['bucket']);
			$bucket_name = $bucket_parts[0];
			$prefix = count($bucket_parts) > 1 ? implode('/', array_slice($bucket_parts, 1)) : '';
			
			// Check if the bucket allows ACLs
			if ($this->settings['use_acl']) {
				try {
					$acl_allowed = $factory->does_bucket_allow_acl($s3_client, $bucket_name);
					if (!$acl_allowed) {
						$this->settings['use_acl'] = false;
						add_settings_error(
							's3_media_sync_settings',
							's3-media-sync-settings-warning',
							__('ACLs are not allowed for this bucket. The "Use ACLs" setting has been automatically disabled.', 's3-media-sync'),
							'warning'
						);
					}
				} catch (\Exception $acl_e) {
					// If we can't check ACL support, assume it's allowed
				}
			}
			
			// Show informational message about the permissions that will be needed
			$policy_example = [
				'Version' => '2012-10-17',
				'Statement' => [
					[
						'Effect' => 'Allow',
						'Action' => [
							's3:ListBucket',
							's3:GetBucketLocation'
						],
						'Resource' => sprintf('arn:aws:s3:::%s', $bucket_name)
					],
					[
						'Effect' => 'Allow',
						'Action' => [
							's3:PutObject',
							's3:GetObject',
							's3:DeleteObject',
							// Only include PutObjectAcl if ACLs are enabled
							$this->settings['use_acl'] ? 's3:PutObjectAcl' : null,
						],
						'Resource' => sprintf(
							'arn:aws:s3:::%s%s%s',
							$bucket_name,
							// Fix for double slash - only add slash if prefix exists
							(!empty($prefix) ? '/' . rtrim($prefix, '/') : ''),
							'/wp-content/uploads/*'
						)
					]
				]
			];
			
			// Remove null values from the policy
			$policy_example['Statement'][1]['Action'] = array_filter($policy_example['Statement'][1]['Action']);
			
			$policy_json = json_encode($policy_example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-info',
				sprintf(
					__('Settings saved. The AWS credentials will need the following permissions to sync media:%s', 's3-media-sync'),
					"\n<pre>" . esc_html($policy_json) . "</pre>"
				),
				'info'  // Show as informational message
			);
			
			return $input;
		} catch (\Aws\Exception\CredentialsException $e) {
			// Handle invalid credentials specifically
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				__('Invalid AWS credentials. Please verify your Access Key ID and Secret Access Key.', 's3-media-sync')
			);
			return $this->settings;
		} catch (\Aws\S3\Exception\S3Exception $e) {
			// Handle AWS-specific errors
			$error_code = $e->getAwsErrorCode() ?: $e->getStatusCode();
			$error_message = $e->getAwsErrorMessage() ?: $e->getMessage();
			
			// Handle credential-related errors specifically
			if (in_array($error_code, ['InvalidAccessKeyId', 'SignatureDoesNotMatch'])) {
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-settings-error',
					__('Invalid AWS credentials. Please verify your Access Key ID and Secret Access Key.', 's3-media-sync')
				);
				return $this->settings;
			}
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				sprintf(
					__('AWS Error: %s (Error code: %s)', 's3-media-sync'),
					$error_message,
					$error_code
				)
			);
			return $this->settings;
		} catch (\Exception $e) {
			// Log only essential error information
			error_log(sprintf(
				'S3 Media Sync client creation error: %s',
				$e->getMessage()
			));
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				sprintf(
					__('Error creating S3 client: %s. Please verify your credentials and settings.', 's3-media-sync'),
					$e->getMessage()
				)
			);
			return $this->settings;
		}
	}

	/**
	 * Render the settings page
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'S3 Media Sync', 's3-media-sync' ); ?></h2>
			
			<div class="s3-media-sync-docs-banner" style="background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-left: 4px solid #007cba;">
				<h3 style="margin-top: 0;"><?php _e('AWS Setup Instructions', 's3-media-sync'); ?></h3>
				<p><?php _e( 'This plugin requires specific AWS permissions to function correctly.', 's3-media-sync' ); ?></p>
				<p>
					<a href="<?php echo esc_url(constant('S3_MEDIA_SYNC_GITHUB_URL')); ?>/blob/main/docs/aws-setup-guide.md" target="_blank" class="button"><?php _e('View on GitHub', 's3-media-sync'); ?></a>
				</p>
			</div>
			
			<form action='options.php' method='post'>
				<?php settings_fields( 's3_media_sync_settings_page' ); ?>
				<?php do_settings_sections( 's3_media_sync_settings_page' ); ?>
				<?php submit_button(); ?>
			</form>
				<?php 
				// Get the saved settings
				$saved_settings = get_option('s3_media_sync_settings', []);
				$has_saved_settings = !empty($saved_settings['bucket']) && 
					!empty($saved_settings['key']) && 
					!empty($saved_settings['secret']) && 
					!empty($saved_settings['region']);
				
				if ($has_saved_settings): 
				?>
					<hr>
					<h3><?php _e('Test S3 Access', 's3-media-sync'); ?></h3>
					<p><?php _e('Click the button below to test if your credentials have the required permissions.', 's3-media-sync'); ?></p>
					<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
						<?php wp_nonce_field('s3_media_sync_test_access'); ?>
						<input type="hidden" name="action" value="s3_media_sync_test_access">
						<?php submit_button(__('Test S3 Access', 's3-media-sync'), 'secondary', 'submit', false); ?>
					</form>
				<?php endif; ?>
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
			__( 'S3 Bucket Region', 's3-media-sync' ),
			[ $this, 's3_region_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[region]' ]
		);

		// Setting: Use ACLs
		add_settings_field(
			'use_acl',
			__( 'Use ACLs', 's3-media-sync' ),
			[ $this, 's3_use_acl_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[use_acl]' ]
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
			'<input type="text" name="s3_media_sync_settings[key]" id="s3_media_sync_settings[key]" value="%s">
			<p class="description">%s</p>',
			esc_attr( $value ),
			__('Enter the Access Key ID from your AWS IAM user.', 's3-media-sync')
		);
	}

	// Render the S3 Secret Access Key text field
	public function s3_secret_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['secret'] ) ? $options['secret'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[secret]" id="s3_media_sync_settings[secret]" value="%s">
			<p class="description">%s</p>',
			esc_attr( $value ),
			__('Enter the Secret Access Key from your AWS IAM user.', 's3-media-sync')
		);
	}

	// Render the S3 Bucket Name text field
	public function s3_bucket_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['bucket'] ) ? $options['bucket'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[bucket]" id="s3_media_sync_settings[bucket]" value="%s">
			<p class="description">%s</p>',
			esc_attr( $value ),
			__('Enter your S3 bucket name. It should be exactly as it appears in the AWS console.', 's3-media-sync')
		);
	}

	// Render the S3 Region text field
	public function s3_region_render() {
		$options = get_option( 's3_media_sync_settings' );
		$value   = ! empty( $options['region'] ) ? $options['region'] : '';
		printf(
			'<input type="text" name="s3_media_sync_settings[region]" id="s3_media_sync_settings[region]" value="%s">
			<p class="description">%s</p>',
			esc_attr( $value ),
			__('Enter the AWS region where your bucket is located (e.g., us-east-1, us-west-2).', 's3-media-sync')
		);
	}

	// Render the Use ACLs checkbox
	public function s3_use_acl_render() {
		$options = get_option('s3_media_sync_settings');
		$checked = isset($options['use_acl']) ? checked($options['use_acl'], '1', false) : checked(true, true, false);
		?>
		<label>
			<input type="checkbox" name="s3_media_sync_settings[use_acl]" id="s3_media_sync_settings[use_acl]" value="1" <?php echo $checked; ?>>
			<?php _e('Enable ACL settings for objects', 's3-media-sync'); ?>
		</label>
		<p class="description">
			<?php _e('Uncheck this if your bucket has "ACLs disabled" setting (Object Ownership set to "Bucket owner enforced").', 's3-media-sync'); ?>
		</p>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				// Toggle visibility of ACL dropdown based on checkbox
				function toggleAclVisibility() {
					if ($('#s3_media_sync_settings\\[use_acl\\]').is(':checked')) {
						$('tr:has(#s3_media_sync_settings\\[object_acl\\])').show();
					} else {
						$('tr:has(#s3_media_sync_settings\\[object_acl\\])').hide();
					}
				}
				
				// Initial state
				toggleAclVisibility();
				
				// Update on change
				$('#s3_media_sync_settings\\[use_acl\\]').on('change', toggleAclVisibility);
			});
		</script>
		<?php
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
		<p class="description"><?php _e('Choose "public-read" if you want your media to be publicly accessible, or "private" for restricted access.', 's3-media-sync'); ?></p>
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

	/**
	 * Handle the test access button submission
	 */
	public function handle_test_access() {
		check_admin_referer('s3_media_sync_test_access');

		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 's3-media-sync'));
		}
		
		// Use the new tester class to handle all the testing logic
		$tester = new S3_Media_Sync_Tester($this->settings);
		$tester->run_tests();

		// Redirect back to the settings page with the results
		set_transient('settings_errors', get_settings_errors(), 30);
		wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
		exit;
	}
} 
