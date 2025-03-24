<?php

use S3_Media_Sync\S3_Media_Sync_Client_Factory;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Region;

class S3_Media_Sync_Settings {
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	public function __construct() {
		$this->settings = get_option('s3_media_sync_settings', []);

		// Sanitize any invalid region in the settings
		if (!empty($this->settings['region'])) {
			try {
				// Attempt to validate the region
				$region = Region::from_string($this->settings['region']);
				// Update with normalized value
				$this->settings['region'] = $region->get_identifier();
			} catch (\S3_Media_Sync\Exceptions\Invalid_Region_Exception $e) {
				// If region is invalid, remove it from settings
				// error_log(sprintf(
				// 	'S3 Media Sync: Removed invalid region "%s" from settings',
				// 	$this->settings['region']
				// ));
				unset($this->settings['region']);
				update_option('s3_media_sync_settings', $this->settings);
			}
		}
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
		
		// Normalize sync_thumbnails option (convert checkbox to boolean)
		// For checkboxes, if they're unchecked, they won't be present in the input at all
		$input['sync_thumbnails'] = isset($input['sync_thumbnails']) ? true : false;

		// Update the current settings with the new input for validation
		$this->settings = $input;

		// Create the client factory
		$factory = new S3_Media_Sync_Client_Factory();

		try {
			// Validate region first
			try {
				$region = Region::from_string($input['region']);
				// Update the region with the normalized value
				$input['region'] = $region->get_identifier();
			} catch (\S3_Media_Sync\Exceptions\Invalid_Region_Exception $e) {
				// Get the list of valid regions with their display names
				$valid_regions_list = array_map(function($region_id) {
					$region = Region::from_string($region_id);
					return sprintf('%s (%s)', $region_id, $region->get_display_name());
				}, Region::get_valid_regions());

				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-settings-error',
					sprintf(
						__('Invalid region: %s. Please select a valid region from the dropdown.', 's3-media-sync'),
						esc_html($input['region'])
					)
				);
				return $this->settings; // Return old settings
			}

			// Create an S3_Bucket object to validate the bucket configuration
			try {
				$bucket = S3_Bucket::from_settings($input);
			} catch (\S3_Media_Sync\Exceptions\Invalid_Bucket_Exception $e) {
				// Get the error message and check for specific error types
				$error_msg = $e->getMessage();
				$friendly_message = '';

				if (strpos($error_msg, 'NoSuchBucket') !== false || strpos($error_msg, '404 Not Found') !== false) {
					$friendly_message = sprintf(
						__('Bucket "%s" does not exist in region %s. Please verify the bucket name and region, then use the Test S3 Access button.', 's3-media-sync'),
						$input['bucket'],
						$region->get_display_name()
					);
				} elseif (strpos($error_msg, 'InvalidAccessKeyId') !== false) {
					$friendly_message = __('Invalid AWS credentials. Please check your access key and secret key, then use the Test S3 Access button.', 's3-media-sync');
				} elseif (strpos($error_msg, 'AccessDenied') !== false) {
					$friendly_message = sprintf(
						__('Access denied to bucket: %s. Please check your IAM permissions, then use the Test S3 Access button.', 's3-media-sync'),
						$input['bucket']
					);
				} else {
					$friendly_message = sprintf(
						__('Could not access bucket: %s. Please use the Test S3 Access button for detailed information.', 's3-media-sync'),
						$input['bucket']
					);
				}

				// Log the full error for debugging
				// error_log('S3 Media Sync: Bucket validation failed - ' . $error_msg);

				// Show only the friendly message to the user
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-settings-error',
					$friendly_message
				);
				return $this->settings; // Return old settings
			}

			// Attempt to create a client - this will throw an exception if credentials are invalid
			$s3_client = $factory->create($input);

			// If we get here, the client was created successfully
			// Attempt to get AWS account info for debugging
			try {
				$debug_info[] = "- Checking AWS account info";
				$sts_client = new \Aws\Sts\StsClient([
					'region' => $input['region'],
					'version' => 'latest',
					'credentials' => [
						'key' => $input['key'],
						'secret' => $input['secret']
					]
				]);
				$identity = $sts_client->getCallerIdentity();
				$debug_info[] = "- AWS Account: " . $identity['Account'];
				$debug_info[] = "- IAM User/Role ARN: " . $identity['Arn'];
			} catch (\Exception $sts_e) {
				$debug_info[] = "- Unable to get AWS account info: " . $sts_e->getMessage();
			}
			
			// Check if the bucket allows ACLs
			if ($input['use_acl']) {
				try {
					$acl_allowed = $factory->does_bucket_allow_acl($s3_client, $bucket->get_name());
					if (!$acl_allowed) {
						$input['use_acl'] = false;
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
						'Resource' => sprintf('arn:aws:s3:::%s', $bucket->get_name())
					],
					[
						'Effect' => 'Allow',
						'Action' => [
							's3:PutObject',
							's3:GetObject',
							's3:DeleteObject'
						],
						'Resource' => sprintf('arn:aws:s3:::%s/*', $bucket->get_name())
					]
				]
			];

			// Configure the stream wrapper to test bucket access
			$factory->configure_stream_wrapper($s3_client, $bucket);

			// Test bucket access
			try {
				$s3_client->headBucket([
					'Bucket' => $bucket->get_name()
				]);
			} catch (\Exception $e) {
				// Log the full error for debugging
				// error_log('S3 Media Sync: Bucket access test failed - ' . $e->getMessage());

				// Determine user-friendly message based on error type
				$error_msg = $e->getMessage();
				if (strpos($error_msg, 'NoSuchBucket') !== false || strpos($error_msg, '404 Not Found') !== false) {
					$friendly_message = sprintf(
						__('Bucket "%s" does not exist or is in a different region. Please verify the bucket name and region, then use the Test S3 Access button.', 's3-media-sync'),
						$bucket->get_name()
					);
				} elseif (strpos($error_msg, 'InvalidAccessKeyId') !== false) {
					$friendly_message = __('Invalid AWS credentials. Please check your access key and secret key, then use the Test S3 Access button.', 's3-media-sync');
				} elseif (strpos($error_msg, 'AccessDenied') !== false) {
					$friendly_message = sprintf(
						__('Access denied to bucket: %s. Please check your IAM permissions, then use the Test S3 Access button.', 's3-media-sync'),
						$bucket->get_name()
					);
				} else {
					$friendly_message = sprintf(
						__('Could not access bucket: %s. Please use the Test S3 Access button for detailed information.', 's3-media-sync'),
						$bucket->get_name()
					);
				}

				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-settings-error',
					$friendly_message
				);
				return $this->settings; // Return old settings
			}

			return $this->settings;

		} catch (\Exception $e) {
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-settings-error',
				sprintf(
					__('Error validating settings: %s', 's3-media-sync'),
					$e->getMessage()
				)
			);
			return $this->settings; // Return old settings
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

		// Setting: Sync Thumbnails
		add_settings_field(
			'sync_thumbnails',
			__( 'Sync Thumbnails', 's3-media-sync' ),
			[ $this, 's3_sync_thumbnails_render' ],
			's3_media_sync_settings_page',
			's3_media_sync_settings',
			[ 'label_for' => 's3_media_sync_settings[sync_thumbnails]' ]
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
		$options = get_option('s3_media_sync_settings');
		$current_value = !empty($options['region']) ? $options['region'] : '';
		
		// Get list of valid regions with their display names
		$valid_regions = [];
		foreach (Region::get_valid_regions() as $region_id) {
			try {
				$region = Region::from_string($region_id);
				$valid_regions[$region_id] = $region->get_display_name();
			} catch (\Exception $e) {
				continue; // Skip invalid regions
			}
		}
		
		// If current value is not in valid regions, add a warning
		if (!empty($current_value) && !isset($valid_regions[$current_value])) {
			echo '<div class="notice notice-warning inline"><p>';
			printf(
				__('Warning: Current region "%s" is not valid. Please select a valid region from the dropdown.', 's3-media-sync'),
				esc_html($current_value)
			);
			echo '</p></div>';
		}
		
		// Create the dropdown HTML
		echo '<select name="s3_media_sync_settings[region]" id="s3_media_sync_settings[region]" required>';
		echo '<option value="">' . esc_html__('-- Select a Region --', 's3-media-sync') . '</option>';
		
		foreach ($valid_regions as $region_id => $display_name) {
			printf(
				'<option value="%s"%s>%s (%s)</option>',
				esc_attr($region_id),
				selected($current_value, $region_id, false),
				esc_html($region_id),
				esc_html($display_name)
			);
		}
		echo '</select>';
		
		printf(
			'<p class="description">%s</p>',
			__('Select the AWS region where your bucket is located.', 's3-media-sync')
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

	// Render the Sync Thumbnails checkbox
	public function s3_sync_thumbnails_render() {
		$options = get_option('s3_media_sync_settings');
		// Explicitly check if the setting exists and is false
		$checked = (isset($options['sync_thumbnails']) && $options['sync_thumbnails'] === false) ? '' : 'checked="checked"';
		?>
		<label>
			<input type="checkbox" name="s3_media_sync_settings[sync_thumbnails]" id="s3_media_sync_settings[sync_thumbnails]" value="1" <?php echo $checked; ?>>
			<?php _e('Sync image thumbnails and size variations to S3', 's3-media-sync'); ?>
		</label>
		<p class="description">
			<?php _e('When enabled, all image size variations will be uploaded to S3. Disable to only upload the original file for faster uploads.', 's3-media-sync'); ?>
		</p>
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
	public function get_settings(): array {
		// Define the expected order of settings
		$ordered_keys = [
			'bucket',
			'key',
			'secret',
			'region',
			'use_acl',
			'object_acl'
		];

		// Create ordered array with only existing settings
		$ordered_settings = [];
		foreach ($ordered_keys as $key) {
			if ($key === 'use_acl') {
				// Always include use_acl with default true
				$ordered_settings[$key] = isset($this->settings[$key]) ? (bool)$this->settings[$key] : true;
			} elseif (array_key_exists($key, $this->settings)) {
				$ordered_settings[$key] = $this->settings[$key];
			}
		}

		return $ordered_settings;
	}

	/**
	 * Update settings
	 *
	 * @param array $settings New settings to save
	 */
	public function update_settings(array $settings): void {
		// Store settings exactly as provided
		$this->settings = $settings;
		update_option('s3_media_sync_settings', $settings);
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
