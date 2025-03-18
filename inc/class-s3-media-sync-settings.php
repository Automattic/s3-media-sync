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
			
			// Show informational message about the permissions that will be needed
			$policy_example = [
				'Version' => '2012-10-17',
				'Statement' => [
					[
						'Effect' => 'Allow',
						'Action' => [
							's3:PutObject',
							's3:GetObject',
							's3:DeleteObject'
						],
						'Resource' => [
							sprintf(
								'arn:aws:s3:::%s%s%s',
								$bucket_name,
								// Fix for double slash - only add slash if prefix exists
								(!empty($prefix) ? '/' . rtrim($prefix, '/') : ''),
								'/wp-content/uploads/*'
							)
						]
					]
				]
			];
			
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

		$factory = new S3_Media_Sync_Client_Factory();
		$debug_info = [];
		
		try {
			// Test 1: Client Creation - Testing credentials validity
			$debug_info[] = "Step 1: Creating S3 client with credentials";
			$debug_info[] = "- Using region from settings: " . $this->settings['region'];
			$s3_client = $factory->create($this->settings);
			$debug_info[] = "- Client created successfully";
			$debug_info[] = "- Actual client region: " . $s3_client->getRegion();
			
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
			$debug_info[] = "- Using bucket: $bucket_name" . ($prefix ? " with prefix: $prefix" : "");
			
			// Test 2: Check if bucket exists
			$debug_info[] = "Step 2: Checking if bucket exists";
			try {
				$bucket_exists = $s3_client->doesBucketExist($bucket_name);
				$debug_info[] = "- Bucket existence check result: " . ($bucket_exists ? "true" : "false");
				
				if (!$bucket_exists) {
					$debug_info[] = "- Standard bucket existence check failed, trying alternative methods";
					
					// Method 1: Try to list bucket location
					try {
						$debug_info[] = "- Attempting to get bucket location";
						$location = $s3_client->getBucketLocation(['Bucket' => $bucket_name]);
						$debug_info[] = "- Bucket location: " . ($location['LocationConstraint'] ?: 'us-east-1');
						// If we get here, the bucket exists
						$bucket_exists = true;
					} catch (\Exception $loc_e) {
						$debug_info[] = "- Failed to get bucket location: " . $loc_e->getMessage();
					}
					
					// Method 2: Try to list bucket contents (already implemented)
					if (!$bucket_exists) {
						$debug_info[] = "- Attempting to list bucket contents as a secondary check";
						try {
							$objects = $s3_client->listObjects([
								'Bucket' => $bucket_name,
								'MaxKeys' => 5,
							]);
							$debug_info[] = "- Successfully listed bucket contents: " . count($objects['Contents'] ?? []) . " objects found";
							$debug_info[] = "- This suggests the bucket exists but doesBucketExist() failed";
						} catch (\Exception $list_e) {
							$debug_info[] = "- Listing bucket contents also failed: " . $list_e->getMessage();
							// Continue with the original exception
							throw new \Aws\S3\Exception\S3Exception("Bucket does not exist", 
								new \Aws\Command("HeadBucket"), ['code' => 'NoSuchBucket']);
						}
					}
					$debug_info[] = "- Bucket exists and is accessible";
				}
			} catch (\Exception $bucket_check_e) {
				$debug_info[] = "- Error checking bucket existence: " . get_class($bucket_check_e) . ": " . $bucket_check_e->getMessage();
				throw $bucket_check_e;
			}
			
			// Test 3: Attempt operations in sequence
			$test_key = '';
			if (!empty($prefix)) {
				$test_key .= rtrim($prefix, '/') . '/';
			}
			$test_key .= 'wp-content/uploads/test.txt';
			$debug_info[] = "Step 3: Attempting to write test object: $test_key";
			
			try {
				// Try to verify bucket access by attempting to put a test object
				$s3_client->putObject([
					'Bucket' => $bucket_name,
					'Key' => $test_key,
					'Body' => 'test',
					'ContentType' => 'text/plain',
				]);
				$debug_info[] = "- Successfully uploaded test object";
			
				// Clean up the test object
				$debug_info[] = "Step 4: Cleaning up test object";
				$s3_client->deleteObject([
					'Bucket' => $bucket_name,
					'Key' => $test_key,
				]);
				$debug_info[] = "- Successfully deleted test object";
				
				// Success message
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-test-success',
					__('Success! Your AWS credentials have the required permissions.', 's3-media-sync'),
					'success'
				);
				
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$error_code = $e->getAwsErrorCode() ?: $e->getStatusCode();
				$error_message = $e->getAwsErrorMessage() ?: $e->getMessage();
				$debug_info[] = "- Operation failed: $error_code - $error_message";
				
				// For certain errors, we want to show a specific message
				switch($error_code) {
					case 'NoSuchBucket':
						$message = sprintf(
							__('The bucket "%s" does not exist. Please verify the bucket name is correct.', 's3-media-sync'),
							$this->settings['bucket']
						);
						break;
						
					case '403':
					case 403:
					case 'Forbidden':
					case 'AccessDenied':
						$message = sprintf(
							__('Access denied. Please verify your permissions for the operation: %s. Error: %s', 's3-media-sync'),
							debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[1]['function'] ?? 'unknown',
							$error_message
						);
						break;
						
					default:
						$message = sprintf(
							__('Error testing S3 access: %s (Error code: %s)', 's3-media-sync'),
							$error_message,
							$error_code
						);
				}
				
				// Add debug info to the error message
				$debug_message = '<br><br><strong>Debug Information:</strong><br>' . implode('<br>', $debug_info);
				
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-test-error',
					$message . $debug_message,
					'error'
				);
			}
		} catch (\Aws\Exception\CredentialsException $e) {
			$debug_info[] = "- Credential error: " . $e->getMessage();
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-test-error',
				sprintf(
					__('Invalid AWS credentials: %s<br><br><strong>Debug Information:</strong><br>%s', 's3-media-sync'),
					$e->getMessage(),
					implode('<br>', $debug_info)
				),
				'error'
			);
		} catch (\Exception $e) {
			$debug_info[] = "- Error: " . get_class($e) . " - " . $e->getMessage();
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-test-error',
				sprintf(
					__('Error creating S3 client: %s<br><br><strong>Debug Information:</strong><br>%s', 's3-media-sync'),
					$e->getMessage(),
					implode('<br>', $debug_info)
				),
				'error'
			);
		}

		// Redirect back to the settings page with the results
		set_transient('settings_errors', get_settings_errors(), 30);
		wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
		exit;
	}
} 
