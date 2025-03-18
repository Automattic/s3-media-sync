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
							's3:PutObjectAcl'
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
			
			// First, check if we can create credentials - this will catch invalid Access Key ID immediately
			try {
				// Try to create credentials separately to catch invalid keys early
				$credentials = new \Aws\Credentials\Credentials(
					$this->settings['key'],
					$this->settings['secret']
				);
				
				// Test the credentials with a small STS call
				$sts_client = new \Aws\Sts\StsClient([
					'region' => $this->settings['region'],
					'version' => 'latest',
					'credentials' => $credentials
				]);
				
				// This will fail immediately with invalid credentials
				try {
					$identity = $sts_client->getCallerIdentity();
					$debug_info[] = "- Credentials validated successfully";
					$debug_info[] = "- AWS Account: " . $identity['Account'];
					$debug_info[] = "- IAM User/Role ARN: " . $identity['Arn'];
				} catch (\Exception $sts_e) {
					// Check if this is actually a credentials error
					$error_message = wp_strip_all_tags($sts_e->getMessage());
					$debug_info[] = "- Credential check failed: " . $error_message;
					
					if (strpos($error_message, 'InvalidClientTokenId') !== false || 
						strpos($error_message, 'SignatureDoesNotMatch') !== false || 
						strpos($error_message, 'InvalidAccessKeyId') !== false) {
						// This is definitely a credentials issue
						throw new \Aws\Exception\CredentialsException(
							'AWS credential validation failed: ' . $error_message
						);
					}
					// Otherwise continue - it might be a permissions issue
				}
				
				// Now create the actual S3 client
				$s3_client = $factory->create($this->settings);
				$debug_info[] = "- S3 client created successfully";
				$debug_info[] = "- Actual client region: " . $s3_client->getRegion();
				
			} catch (\Aws\Exception\CredentialsException $ce) {
				// Catch credential exceptions immediately to avoid running further tests
				$debug_info[] = "- Credential error detected in Step 1";
				throw $ce;
			}
			
			// Extract the actual bucket name if there's a path prefix
			$bucket_parts = explode('/', $this->settings['bucket']);
			$bucket_name = $bucket_parts[0];
			$prefix = count($bucket_parts) > 1 ? implode('/', array_slice($bucket_parts, 1)) : '';
			$debug_info[] = "- Using bucket: $bucket_name" . ($prefix ? " with prefix: $prefix" : "");
			
			// Test 2: Check if bucket exists
			$debug_info[] = "Step 2: Checking if bucket exists and is accessible";
			try {
				$bucket_exists = $s3_client->doesBucketExist($this->settings['bucket']);
				if ($bucket_exists) {
					$debug_info[] = "- Bucket '{$this->settings['bucket']}' exists and is accessible";
				} else {
					$bucket_error = true;
					$debug_info[] = "- Bucket '{$this->settings['bucket']}' does not exist or is not accessible";
					
					// For bucket existence failures, try to determine if it's a region or name issue
					// First check if we can resolve the regional endpoint (region issue check)
					try {
						// Many AWS regions can be resolved through s3.{region}.amazonaws.com
						// but some regions like us-east-1 use s3.amazonaws.com
						$domain = "{$this->settings['region']}.amazonaws.com";
						if ($this->settings['region'] === 'us-east-1') {
							$domain = "s3.amazonaws.com"; // Special case for us-east-1
						}
						
						$debug_info[] = "- Testing DNS resolution for region endpoint: $domain";
						$ip = gethostbyname($domain);
						
						// If we get here with a resolved IP that's different from the domain name,
						// the region exists but the bucket name is likely wrong
						if ($ip !== $domain) {
							$debug_info[] = "- Region endpoint resolves correctly to $ip";
							
							// Now check if the bucket exists in a different region
							try {
								// First verify the region by trying a small operation
								// If this fails with NoSuchBucket, then the bucket name is wrong
								// If it fails with a region redirect, then we know the region is wrong
								
								$s3_client->headBucket([
									'Bucket' => $this->settings['bucket']
								]);
								
								// If we get here, the bucket exists and is accessible
								$debug_info[] = "- Bucket actually does exist! Might be a permission issue.";
								throw new \Exception("OPERATION_ERROR: The bucket exists but there might be permission issues accessing it.");
								
							} catch (\Aws\S3\Exception\S3Exception $head_e) {
								$error_code = $head_e->getAwsErrorCode();
								$debug_info[] = "- Detailed bucket check error: $error_code - " . $head_e->getMessage();
								
								if (strpos($head_e->getMessage(), "PermanentRedirect") !== false) {
									// Bucket exists but in a different region
									$debug_info[] = "- Bucket exists but in a different region";
									throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' is incorrect for this bucket. The bucket exists but is in a different region.");
								} else if ($error_code === 'NoSuchBucket' || strpos($head_e->getMessage(), "NoSuchBucket") !== false) {
									// Bucket truly doesn't exist
									$debug_info[] = "- Bucket confirmed not to exist (NoSuchBucket)";
									throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
								} else {
									// This could be a permission issue
									$debug_info[] = "- Bucket existence check failed with access error";
									throw new \Exception("OPERATION_ERROR: Unable to access the bucket. Please check your permissions.");
								}
							}
							
						} else {
							// If we can't resolve the domain, it's likely a region issue
							$debug_info[] = "- Failed to resolve region endpoint domain. Region may be invalid.";
							throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
						}
					} catch (\Exception $dns_e) {
						// If DNS check itself fails, fall back to original exception
						$debug_info[] = "- DNS resolution check failed: " . $dns_e->getMessage();
						
						// Try a known working region to better determine if it's a region or bucket issue
						try {
							$debug_info[] = "- Attempting to check bucket with fallback region (us-east-1)";
							$alt_client = new \Aws\S3\S3Client([
								'region' => 'us-east-1',
								'version' => 'latest',
								'credentials' => new \Aws\Credentials\Credentials(
									$this->settings['key'],
									$this->settings['secret']
								)
							]);
							
							// Try to access the bucket with the alternate region
							$bucket_exists = $alt_client->doesBucketExist($this->settings['bucket']);
							
							if ($bucket_exists) {
								// If the bucket exists with a different region, then the region was wrong
								$debug_info[] = "- Bucket found using alternate region. Original region is likely incorrect.";
								throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' is incorrect for this bucket. The bucket exists but is in a different region.");
							} else {
								// If the bucket doesn't exist with the alternate region either, then the bucket name is likely wrong
								$debug_info[] = "- Bucket not found using alternate region. Bucket name is likely incorrect.";
								throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
							}
						} catch (\Exception $alt_e) {
							// Preserve any specific error messages from inner exceptions
							if (strpos($alt_e->getMessage(), "REGION_ERROR:") === 0 ||
								strpos($alt_e->getMessage(), "BUCKET_NAME_ERROR:") === 0 ||
								strpos($alt_e->getMessage(), "OPERATION_ERROR:") === 0) {
								throw $alt_e;
							}
							
							// If we get here with no specific error type, default to bucket name error with a complete message
							$debug_info[] = "- Alternate region check failed with error: " . $alt_e->getMessage();
							throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
						}
					}
				}
			} catch (\Exception $e) {
				$bucket_error = true;
				$error_message = wp_strip_all_tags($e->getMessage());
				$debug_info[] = "- Bucket check error: $error_message";
				
				// Only do additional error detection if not already classified
				if (strpos($error_message, "REGION_ERROR:") === false && 
				    strpos($error_message, "BUCKET_NAME_ERROR:") === false &&
				    strpos($error_message, "BUCKET_ERROR:") === false) {
					
					// Check for specific error patterns to provide better messages
					$complete_message = $e->getMessage();
					
					// Region errors have very specific signatures
					if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
						throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
					} 
					// NoSuchBucket is a clear indicator of wrong bucket name with correct region
					else if (strpos($complete_message, "404 Not Found") !== false || 
							 strpos($complete_message, "NoSuchBucket") !== false) {
						throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
					} 
					// Other bucket errors
					else {
						throw new \Exception("BUCKET_ERROR: The bucket '{$this->settings['bucket']}' could not be accessed. Please verify your bucket settings and permissions.");
					}
				} else {
					// Re-throw already classified errors
					throw $e;
				}
			}
			
			// Test 3: Try a basic operation
			try {
				$debug_info[] = "Step 3: Testing basic S3 operations";
				
				// Test operation
				$test_key = 'wp-content/uploads/s3-media-sync-test-' . time() . '.txt';
				$debug_info[] = "- Attempting to put a test object: $test_key";
				
				$s3_client->putObject([
					'Bucket' => $bucket_name,
					'Key'    => $test_key,
					'Body'   => 'This is a test file from S3 Media Sync',
				]);
				$debug_info[] = "- Successfully uploaded test object";
				
				$debug_info[] = "- Attempting to get the test object";
				$s3_client->getObject([
					'Bucket' => $bucket_name,
					'Key'    => $test_key,
				]);
				$debug_info[] = "- Successfully downloaded test object";
				
				$debug_info[] = "- Attempting to delete the test object";
				$s3_client->deleteObject([
					'Bucket' => $bucket_name,
					'Key'    => $test_key,
				]);
				$debug_info[] = "- Successfully deleted test object";
				
				$debug_info[] = "All tests completed successfully!";
				$success = true;
				
				// Add success message
				$technical_details = sprintf(
					'<div class="s3-media-sync-technical-details">
						<p><a href="#" onclick="jQuery(\'#success-details-%s\').toggle(); return false;">%s</a></p>
						<div id="success-details-%s" style="display: none;">
							<p><strong>%s</strong></p>
							<pre>%s</pre>
						</div>
					</div>',
					substr(md5(time()), 0, 6),
					__('Show Details', 's3-media-sync'),
					substr(md5(time()), 0, 6),
					__('Test Steps:', 's3-media-sync'),
					implode("\n", $debug_info)
				);
				
				add_settings_error(
					's3_media_sync_settings',
					's3-media-sync-test-success',
					__('Success! Your AWS credentials have the required permissions.', 's3-media-sync') . $technical_details,
					'success'
				);
			} catch (\Exception $e) {
				// Check if this is a credential error
				$error_message = wp_strip_all_tags($e->getMessage());
				
				if (strpos($error_message, 'InvalidClientTokenId') !== false || 
					strpos($error_message, 'SignatureDoesNotMatch') !== false || 
					strpos($error_message, 'InvalidAccessKeyId') !== false) {
					// Rethrow as a credentials exception
					$filtered_debug_info = array_filter($debug_info, function($line) {
						return strpos($line, "Credential error detected") === false;
					});
					$debug_info = $filtered_debug_info;
					$debug_info[] = "- Credential error detected in operation test";
					throw new \Exception("CREDENTIAL_ERROR: Your AWS access credentials are invalid. Please check your Access Key and Secret Key.");
				}
				
				if ($e instanceof \Aws\S3\Exception\S3Exception) {
					$error_code = $e->getAwsErrorCode() ?: $e->getStatusCode();
					$error_message = $e->getAwsErrorMessage() ?: $e->getMessage();
					
					// Sanitize the error message to avoid HTML issues
					$error_message = wp_strip_all_tags($error_message);
					$debug_info[] = "- Operation failed: $error_code - $error_message";
					
					// Complete message for detailed pattern matching
					$complete_message = $e->getMessage();
					
					// For certain errors, we want to throw specific error types
					if ($error_code === 'NoSuchBucket' || strpos($complete_message, "NoSuchBucket") !== false) {
						throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
					} else if ($error_code == '403' || $error_code == 403 || $error_code == 'Forbidden' || $error_code == 'AccessDenied') {
						throw new \Exception("OPERATION_ERROR: Access denied. The AWS user does not have sufficient permissions for S3 operations.");
					} else if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
						throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
					} else {
						throw new \Exception("OPERATION_ERROR: Error testing S3 access: $error_code. Please check your bucket configuration and permissions.");
					}
				} else {
					// Generic error handling
					$complete_message = $e->getMessage();
					
					// Check for more specific error patterns
					if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
						throw new \Exception("REGION_ERROR: The region '{$this->settings['region']}' appears to be invalid. Please verify your region setting is correct.");
					} else if (strpos($complete_message, "404 Not Found") !== false || 
							   strpos($complete_message, "NoSuchBucket") !== false) {
						throw new \Exception("BUCKET_NAME_ERROR: The bucket '{$this->settings['bucket']}' does not exist. Please verify your bucket name is correct.");
					} else {
						throw new \Exception("OPERATION_ERROR: Error testing S3 access. Please verify your bucket settings and permissions.");
					}
					
					$debug_info[] = "- Operation error: " . get_class($e) . " - " . wp_strip_all_tags($e->getMessage());
				}
			}
		} catch (\Aws\Exception\CredentialsException $e) {
			// Sanitize the error message to avoid HTML issues
			$error_msg = wp_strip_all_tags($e->getMessage());
			
			// Clean up debug info to avoid duplicate error messages
			// If we already have a credential error detected message, we don't need to add the full error again
			$contains_credential_error = false;
			foreach ($debug_info as $line) {
				if (strpos($line, "Credential error detected") !== false) {
					$contains_credential_error = true;
					break;
				}
			}
			
			if (!$contains_credential_error) {
				$debug_info[] = "- Credential error: " . $error_msg;
			}
			
			// Filter out all lines containing "Credential error detected" for cleaner output
			$filtered_debug_info = array_filter($debug_info, function($line) {
				return strpos($line, "Credential error detected") === false;
			});
			
			// Create a simpler, more user-friendly message
			$simple_message = __('Your AWS credentials appear to be invalid. Please verify your Access Key ID and Secret Access Key are correct.', 's3-media-sync');
			$error_type = "credential";
			
			// Create unique ID for the error details element
			$error_id = 'aws-error-credential-' . substr(md5(time()), 0, 6);
			
			// Create collapsible technical details with clean debug info
			$technical_details = sprintf(
				'<div class="s3-media-sync-technical-details">
					<p><a href="#" onclick="jQuery(\'#%s\').toggle(); return false;">%s</a></p>
					<div id="%s" style="display: none;">
						<p><strong>%s</strong></p>
						<pre>%s</pre>
					</div>
				</div>',
				$error_id,
				__('Show Technical Details', 's3-media-sync'),
				$error_id,
				__('Debug Steps:', 's3-media-sync'),
				implode("\n", $filtered_debug_info)
			);
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-test-error',
				$simple_message . $technical_details,
				'error'
			);
		} catch (\Exception $e) {
			$error_message = $e->getMessage();
			$error_type = "";
			
			// Extract error type if it exists
			if (strpos($error_message, "REGION_ERROR:") === 0) {
				$error_type = "region";
				$error_message = str_replace("REGION_ERROR: ", "", $error_message);
				$simple_message = $error_message;
			} else if (strpos($error_message, "BUCKET_NAME_ERROR:") === 0) {
				$error_type = "bucket_name"; // Use a distinct type for bucket name errors
				$error_message = str_replace("BUCKET_NAME_ERROR: ", "", $error_message);
				$simple_message = $error_message;
			} else if (strpos($error_message, "BUCKET_ERROR:") === 0) {
				$error_type = "bucket"; // Generic bucket error
				$error_message = str_replace("BUCKET_ERROR: ", "", $error_message);
				$simple_message = $error_message;
			} else if (strpos($error_message, "CREDENTIAL_ERROR:") === 0) {
				$error_type = "credential";
				$error_message = str_replace("CREDENTIAL_ERROR: ", "", $error_message);
				$simple_message = $error_message;
			} else if (strpos($error_message, "OPERATION_ERROR:") === 0) {
				$error_type = "operation";
				$error_message = str_replace("OPERATION_ERROR: ", "", $error_message);
				$simple_message = $error_message;
			} else {
				// Generic error handler - more aggressive pattern detection
				$complete_message = $error_message;
				if (strpos($complete_message, "cURL error 6: Could not resolve host") !== false) {
					$error_type = "region"; // Explicitly set error type
					$simple_message = sprintf(
						__('The region "%s" appears to be invalid. Please verify your region setting is correct.', 's3-media-sync'),
						$this->settings['region']
					);
				} else if (strpos($complete_message, "404 Not Found") !== false || 
						   strpos($complete_message, "NoSuchBucket") !== false) {
					$error_type = "bucket_name"; // Explicitly set error type
					$simple_message = sprintf(
						__('The bucket "%s" does not exist. Please verify your bucket name is correct.', 's3-media-sync'),
						$this->settings['bucket']
					);
				} else {
					$error_type = "generic"; // Mark as truly generic
					$simple_message = __('Error testing S3 access. Please verify your settings.', 's3-media-sync');
				}
			}
			
			// Filter out all lines containing "Credential error detected" for cleaner output
			$filtered_debug_info = array_filter($debug_info, function($line) {
				return strpos($line, "Credential error detected") === false;
			});
			
			// Ensure simple_message is always set, even if somehow missed in error extraction
			if (empty($simple_message)) {
				// Detect error type from debug info as a fallback
				$debug_text = implode("\n", $filtered_debug_info);
				
				if (strpos($debug_text, "REGION_ERROR:") !== false || 
					strpos($debug_text, "Failed to resolve region endpoint") !== false) {
					$simple_message = sprintf(
						__('The region "%s" appears to be invalid. Please verify your region setting is correct.', 's3-media-sync'),
						$this->settings['region']
					);
					$error_type = "region";
				} 
				else if (strpos($debug_text, "BUCKET_NAME_ERROR:") !== false || 
						 strpos($debug_text, "Bucket not found") !== false || 
						 strpos($debug_text, "NoSuchBucket") !== false) {
					$simple_message = sprintf(
						__('The bucket "%s" does not exist. Please verify your bucket name is correct.', 's3-media-sync'),
						$this->settings['bucket']
					);
					$error_type = "bucket_name";
				}
				else if (strpos($debug_text, "OPERATION_ERROR:") !== false) {
					$simple_message = __('Access denied. Check that your AWS user has sufficient permissions for S3 operations.', 's3-media-sync');
					$error_type = "operation";
				}
				else {
					// Last resort fallback
					$simple_message = __('Error testing S3 access. Please verify your bucket settings and permissions.', 's3-media-sync');
					$error_type = "generic";
				}
			}
			
			// Create unique ID for the error details element with more specific error types
			$error_id = 'aws-error-';
			
			// Add more specific identification to the error ID
			if ($error_type === 'region') {
				$error_id .= 'region-';
			} else if ($error_type === 'bucket_name') {
				$error_id .= 'bucket-name-';
			} else if ($error_type === 'bucket') {
				$error_id .= 'bucket-general-';
			} else if ($error_type === 'credential') {
				$error_id .= 'credential-';
			} else if ($error_type === 'operation') {
				$error_id .= 'operation-';
			} else {
				$error_id .= 'general-';
			}
			
			$error_id .= substr(md5(time()), 0, 6);
			
			// Add technical details in a collapsible section
			$technical_details = sprintf(
				'<div class="s3-media-sync-technical-details">
					<p><a href="#" onclick="jQuery(\'#%s\').toggle(); return false;">%s</a></p>
					<div id="%s" style="display: none;">
						<p><strong>%s</strong></p>
						<pre>%s</pre>
					</div>
				</div>',
				$error_id,
				__('Show Technical Details', 's3-media-sync'),
				$error_id,
				__('Debug Steps:', 's3-media-sync'),
				implode("\n", $filtered_debug_info)
			);
			
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-test-error',
				$simple_message . $technical_details,
				'error'
			);
		}

		// At the end of the function, just before the redirect, add this block to handle the case where no errors occurred
		if (!isset($success) && empty(get_settings_errors('s3_media_sync_settings'))) {
			// This shouldn't happen, but just in case we missed setting a success message
			add_settings_error(
				's3_media_sync_settings',
				's3-media-sync-test-success',
				__('Success! Your AWS credentials have the required permissions.', 's3-media-sync'),
				'success'
			);
		}

		// Redirect back to the settings page with the results
		set_transient('settings_errors', get_settings_errors(), 30);
		wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
		exit;
	}
} 
