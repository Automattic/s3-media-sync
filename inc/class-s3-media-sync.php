<?php

use S3_Media_Sync\S3_Media_Sync_Client_Factory;
use S3_Media_Sync\Value_Objects\S3_Bucket;

class S3_Media_Sync {
	private $settings;
	private $settings_handler;
	private $s3_client;
	private $bucket;

	/**
	 * Constructor.
	 *
	 * @param S3_Media_Sync_Settings $settings_handler Settings handler instance.
	 */
	public function __construct( S3_Media_Sync_Settings $settings_handler ) {
		$this->settings_handler = $settings_handler;
		$this->settings        = $this->settings_handler->get_settings();
		$this->bucket         = S3_Bucket::from_settings($this->settings);
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
		return $this->bucket->get_name();
	}

	public function get_s3_bucket_url() {
		return 's3://' . $this->bucket->get_name();
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
		if (!$this->settings_handler->has_required_settings()) {
			// error_log('S3 Media Sync: Required settings missing - hooks not registered');
			return;
		}

		try {
			// Register and configure the stream wrapper
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->bucket);

			// Hook into WordPress media handling
			// These hooks match the original plugin behavior and test expectations
			add_filter( 'wp_handle_upload', [ $this, 'add_attachment_to_s3' ], 10, 2 );
			add_action( 'delete_attachment', [ $this, 'delete_attachment_from_s3' ], 10, 1 );
			
			// For image editor saves (cropping, etc.)
			add_filter( 'wp_save_image_editor_file', [ $this, 'add_updated_attachment_to_s3' ], 10, 5 );
			
			// For image sizes and thumbnails
			add_filter( 'wp_update_attachment_metadata', [ $this, 'sync_attachment_metadata' ], 10, 2 );
			
			// Debug log to confirm hook registration
			$sync_thumbnails_status = isset($this->settings['sync_thumbnails']) ? 
				($this->settings['sync_thumbnails'] !== false ? 'enabled' : 'disabled') : 'enabled (default)';
			// error_log('S3 Media Sync: Hooks registered for uploads, deletion, image editing, and thumbnail generation. Thumbnail syncing is ' . $sync_thumbnails_status);
		} catch (\Exception $e) {
			// error_log(sprintf(
			// 	'S3 Media Sync: Failed to configure for bucket "%s" in region %s (%s) - %s',
			// 	$this->bucket->get_name(),
			// 	$this->bucket->get_region()->get_identifier(),
			// 	$this->bucket->get_region()->get_display_name(),
			// 	$e->getMessage()
			// ));
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
	 * Add an attachment file to S3
	 *
	 * @param array  $upload Upload info
	 * @param string $context Upload context
	 *
	 * @return array Upload info
	 */
	public function add_attachment_to_s3($upload, $context = 'upload') {
		try {
			// Ensure we have a valid S3 client
			if (!$this->is_s3_configured()) {
				// Integration test relies on this logging
				error_log('S3 Media Sync: Failed to upload - S3 is not properly configured');
				return $upload;
			}

			// Get the file path
			$file_path = $upload['file'];
			if (!file_exists($file_path)) {
				// error_log(sprintf('S3 Media Sync: Failed to upload - File not found: %s', $file_path));
				return $upload;
			}

			// Get uploads directory info
			$uploads = wp_upload_dir();
			
			// Create the relative path within the bucket ensuring it has the wp-content/uploads prefix
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $file_path);
			$s3_key = 'wp-content/uploads/' . $file_subpath;

			try {
				// Create the upload command
				$args = [
					'Bucket' => $this->bucket->get_name(),
					'Key' => $s3_key,
					'SourceFile' => $file_path
				];

				// Add ACL if enabled
				if ($this->settings_handler->get_setting('use_acl', true)) {
					$args['ACL'] = $this->settings_handler->get_setting('object_acl', 'private');
				}

				// Attempt to upload the file
				$this->get_client_factory()->create($this->settings)->putObject($args);

				// error_log(sprintf('S3 Media Sync: Successfully uploaded %s to s3://%s/%s',
				// 	$file_path,
				// 	$this->bucket->get_name(),
				// 	$s3_key
				// ));
			} catch (\Aws\S3\Exception\S3Exception $e) {
				// error_log(sprintf('S3 Media Sync: S3 upload error [%s] - %s',
				// 	$e->getAwsErrorCode(),
				// 	$e->getMessage()
				// ));
				return $upload;
			}

			return $upload;
		} catch (\Exception $e) {
			// error_log(sprintf('S3 Media Sync: Failed to upload - %s', $e->getMessage()));
			return $upload;
		}
	}

	/**
	 * Add an image editor updated attachment to S3
	 *
	 * @param string $filename File name
	 * @param resource $image Image resource
	 * @param string $mime_type Mime type
	 * @param int $post_id Post ID
	 * @param string $size Size name
	 *
	 * @return string File name
	 */
	public function add_updated_attachment_to_s3( $filename, $image, $mime_type, $post_id, $size = null ) {
		// Check if file exists after saving
		if (!file_exists($filename)) {
			// error_log('S3 Media Sync: Image editor file does not exist: ' . $filename);
			return $filename;
		}

		// Get file info for logging
		$file_size = filesize($filename);
		// error_log('S3 Media Sync: Processing image editor file - File: ' . $filename . ', Size: ' . $file_size . ' bytes, MIME: ' . $mime_type . ', Post ID: ' . $post_id . ', Size variant: ' . ($size ? $size : 'original'));

		// If this is a size/thumbnail and thumbnail syncing is disabled, skip it
		if ($size && isset($this->settings['sync_thumbnails']) && $this->settings['sync_thumbnails'] === false) {
			// error_log('S3 Media Sync: Skipping image size "' . $size . '" due to sync_thumbnails setting disabled');
			return $filename;
		}

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			// error_log('S3 Media Sync: S3 is not properly configured, skipping image editor upload');
			return $filename;
		}

		try {
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->bucket);
			
			// Get uploads directory info
			$uploads = wp_upload_dir();
			
			// Create the relative path within the bucket ensuring it has the wp-content/uploads prefix
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $filename);
			$relative_path = 'wp-content/uploads/' . $file_subpath;
			
			// error_log('S3 Media Sync: Processing edited image: ' . $filename . ' -> S3:' . $relative_path);
			
			// First try using stream wrapper
			$uploaded = $this->try_stream_wrapper_upload($s3_client, $filename, $relative_path);
			
			// If stream wrapper failed, try direct API
			if (!$uploaded) {
				// error_log('S3 Media Sync: Stream wrapper upload failed for edited image, trying direct API');
				$uploaded = $this->try_direct_s3_upload($s3_client, $filename, $relative_path);
			}
			
			if ($uploaded) {
				// error_log('S3 Media Sync: Successfully uploaded edited image to S3');
			} else {
				// error_log('S3 Media Sync: Failed to upload edited image to S3 after all attempts');
			}
		} catch (\Exception $e) {
			// error_log('S3 Media Sync image editor exception: ' . $e->getMessage());
		}
		
		return $filename;
	}

	/**
	 * Trigger a delete in S3 to keep the media backups in sync
	 *
	 * @param int $post_id Attachment post ID to delete
	 * @return bool Whether the deletion was successful
	 */
	public function delete_attachment_from_s3($post_id) {
		try {
			// First, get the metadata to find all thumbnails
			$metadata = wp_get_attachment_metadata($post_id);
			
			// Prepare a list of objects to delete
			$delete_paths = [];
			
			// Grab the path for the attachment -- this is the S3 key
			$upload_dir = wp_upload_dir();
			$source = $upload_dir['baseurl'];
			$attachment_url = wp_get_attachment_url($post_id);
			
			if (empty($attachment_url)) {
				return false;
			}
			
			// Extract the path relative to the uploads directory
			$relative_path = str_replace($source, '', $attachment_url);
			
			// Use the full path with wp-content/uploads prefix to match IAM policy
			$main_path = 'wp-content/uploads' . $relative_path;
			$delete_paths[] = $main_path;
			
			// error_log('S3 Media Sync: Preparing to delete attachment from S3: ' . $main_path);
			
			// If we have metadata and thumbnails, add them to the delete list
			if (!empty($metadata) && !empty($metadata['file']) && !empty($metadata['sizes'])) {
				$file_dir = dirname($metadata['file']);
				$file_dir = empty($file_dir) ? '' : trailingslashit($file_dir);
				
				// Add each thumbnail to the delete list
				foreach ($metadata['sizes'] as $size => $size_info) {
					if (!empty($size_info['file'])) {
						$thumb_path = 'wp-content/uploads/' . $file_dir . $size_info['file'];
						$delete_paths[] = $thumb_path;
						// error_log('S3 Media Sync: Adding thumbnail for deletion: ' . $thumb_path);
					}
				}
			} else {
				// error_log('S3 Media Sync: No thumbnail metadata found for attachment ID ' . $post_id);
			}
			
			$bucket = $this->get_s3_bucket();
			
			// Handle bucket with path prefix
			if (strpos($bucket, '/') !== false) {
				$bucket_parts = explode('/', $bucket, 2);
				$bucket = $bucket_parts[0];
				$prefix = trailingslashit($bucket_parts[1]);
				
				// Apply prefix to all paths
				foreach ($delete_paths as $i => $path) {
					$delete_paths[$i] = $prefix . $path;
				}
			}
			
			// List objects with matching prefix to capture any other variations
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			
			// Create list of objects to delete
			$delete_objects = [];
			
			// First check for each specific path we know about
			foreach ($delete_paths as $path) {
				try {
					// Check if object exists before adding to delete list
					$s3_client->headObject([
						'Bucket' => $bucket,
						'Key' => $path
					]);
					$delete_objects[] = ['Key' => $path];
					// error_log('S3 Media Sync: Object exists, adding for deletion: ' . $path);
				} catch (\Exception $e) {
					// error_log('S3 Media Sync: Object does not exist or cannot be accessed: ' . $path);
				}
			}
			
			// Also list the directory to catch any files we might have missed
			// This helps with edited images or other variants
			$base_prefix = dirname($main_path);
			$base_prefix = trailingslashit($base_prefix);
			$filename = basename($main_path);
			$filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
			
			try {
				$objects = $s3_client->listObjects([
					'Bucket' => $bucket,
					'Prefix' => $base_prefix
				]);
				
				if (isset($objects['Contents']) && !empty($objects['Contents'])) {
					foreach ($objects['Contents'] as $object) {
						$object_key = $object['Key'];
						$object_filename = basename($object_key);
						
						// If the object filename contains our base filename, it's likely a variant
						if (strpos($object_filename, $filename_without_ext) !== false && 
							!in_array(['Key' => $object_key], $delete_objects)) {
							$delete_objects[] = ['Key' => $object_key];
							// error_log('S3 Media Sync: Found related object for deletion: ' . $object_key);
						}
					}
				}
			} catch (\Exception $e) {
				// error_log('S3 Media Sync: Error listing objects: ' . $e->getMessage());
			}
			
			// If nothing to delete, return success
			if (empty($delete_objects)) {
				// error_log('S3 Media Sync: No objects found to delete');
				return true;
			}
			
			// Delete the objects
			$result = $s3_client->deleteObjects([
				'Bucket' => $bucket,
				'Delete' => [
					'Objects' => $delete_objects
				]
			]);
			
			// error_log('S3 Media Sync: Successfully deleted ' . count($delete_objects) . ' objects from S3');
			return true;
		} catch (\Exception $e) {
			// error_log('S3 Media Sync delete error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Sync thumbnail images and other sizes based on attachment metadata
	 *
	 * @param array $metadata Attachment metadata
	 * @param int $attachment_id Attachment ID
	 * @return array The original metadata
	 */
	public function sync_attachment_metadata($metadata, $attachment_id) {
		// Skip if metadata is empty or not valid
		if (empty($metadata) || !is_array($metadata)) {
			return $metadata;
		}

		// Skip if the main file path is missing
		if (empty($metadata['file'])) {
			// error_log('S3 Media Sync: No file path in metadata for attachment ID ' . $attachment_id);
			return $metadata;
		}

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			// error_log('S3 Media Sync: S3 is not properly configured, skipping metadata sync');
			return $metadata;
		}

		try {
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->bucket);
			
			$wp_uploads = wp_upload_dir();
			$base_dir = $wp_uploads['basedir'];
			$file_dir = dirname($metadata['file']);
			
			// Check if we're syncing thumbnails - default to false if not set
			$sync_thumbnails = isset($this->settings['sync_thumbnails']) && $this->settings['sync_thumbnails'] === true;
			
			if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
				// error_log('S3 Media Sync: Processing attachment metadata for ID ' . $attachment_id . ' with ' . 
				//     count($metadata['sizes']) . ' sizes. Thumbnail syncing is ' . ($sync_thumbnails ? 'enabled' : 'disabled'));
			}
			
			// Always sync the original file if it exists and wasn't previously uploaded
			$original_file_path = trailingslashit($base_dir) . $metadata['file'];
			
			if (file_exists($original_file_path)) {
				$original_s3_key = 'wp-content/uploads/' . $metadata['file'];
				// error_log('S3 Media Sync: Processing original file: ' . $original_file_path . ' -> S3:' . $original_s3_key);
				
				// Try to upload the original file
				$uploaded = $this->try_stream_wrapper_upload($s3_client, $original_file_path, $original_s3_key);
				
				if (!$uploaded) {
					// error_log('S3 Media Sync: Stream wrapper upload failed for original file, trying direct API');
					$uploaded = $this->try_direct_s3_upload($s3_client, $original_file_path, $original_s3_key);
				}
				
				if ($uploaded) {
					// error_log('S3 Media Sync: Successfully uploaded original file to S3');
				} else {
					// error_log('S3 Media Sync: Failed to upload original file to S3 after all attempts');
				}
			}
			
			// Process thumbnails and size variations only if the setting is enabled
			if ($sync_thumbnails && !empty($metadata['sizes']) && is_array($metadata['sizes'])) {
				// Process each size
				foreach ($metadata['sizes'] as $size => $size_info) {
					if (empty($size_info['file'])) {
						continue;
					}
					
					// Construct source and destination paths
					$size_file_path = trailingslashit($base_dir) . (empty($file_dir) ? '' : trailingslashit($file_dir)) . $size_info['file'];
					$size_s3_key = 'wp-content/uploads/' . (empty($file_dir) ? '' : trailingslashit($file_dir)) . $size_info['file'];
					
					if (!file_exists($size_file_path)) {
						// error_log("S3 Media Sync: Size file doesn't exist: " . $size_file_path);
						continue;
					}
					
					// error_log('S3 Media Sync: Processing size ' . $size . ': ' . $size_file_path . ' -> S3:' . $size_s3_key);
					
					// First try using stream wrapper
					$uploaded = $this->try_stream_wrapper_upload($s3_client, $size_file_path, $size_s3_key);
					
					// If stream wrapper failed, try direct API
					if (!$uploaded) {
						// error_log('S3 Media Sync: Stream wrapper upload failed for size ' . $size . ', trying direct API');
						$uploaded = $this->try_direct_s3_upload($s3_client, $size_file_path, $size_s3_key);
					}
					
					if ($uploaded) {
						// error_log('S3 Media Sync: Successfully uploaded size ' . $size . ' to S3');
					} else {
						// error_log('S3 Media Sync: Failed to upload size ' . $size . ' to S3 after all attempts');
					}
				}
			} else if (!$sync_thumbnails && !empty($metadata['sizes'])) {
				// error_log('S3 Media Sync: Skipping ' . count($metadata['sizes']) . ' thumbnail sizes due to sync_thumbnails setting disabled');
			}
		} catch (\Exception $e) {
			// error_log('S3 Media Sync metadata sync exception: ' . $e->getMessage());
		}
		
		return $metadata;
	}

	/**
	 * Check if the S3 client and stream wrapper are properly configured
	 *
	 * @return bool Whether the S3 client is properly configured
	 */
	protected function is_s3_configured() {
		try {
			// Create a new client factory
			$factory = $this->get_client_factory();
			
			// Create a new S3 client
			$s3_client = $factory->create($this->settings);
			
			// Test bucket access
			try {
				$s3_client->headBucket([
					'Bucket' => $this->bucket->get_name()
				]);
				return true;
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$error_message = $e->getMessage();
				if (strpos($error_message, 'NoSuchBucket') !== false || strpos($error_message, '404 Not Found') !== false) {
					// error_log(sprintf(
					// 	'S3 Media Sync: Bucket "%s" does not exist in region %s (%s)',
					// 	$this->bucket->get_name(),
					// 	$this->bucket->get_region()->get_identifier(),
					// 	$this->bucket->get_region()->get_display_name()
					// ));
				} elseif (strpos($error_message, 'InvalidAccessKeyId') !== false) {
					// error_log('S3 Media Sync: Invalid AWS credentials');
				} elseif (strpos($error_message, 'AccessDenied') !== false) {
					// error_log(sprintf(
					// 	'S3 Media Sync: Access denied to bucket "%s" in region %s (%s)',
					// 	$this->bucket->get_name(),
					// 	$this->bucket->get_region()->get_identifier(),
					// 	$this->bucket->get_region()->get_display_name()
					// ));
				} else {
					// error_log(sprintf(
					// 	'S3 Media Sync: Error accessing bucket "%s" in region %s (%s) - %s',
					// 	$this->bucket->get_name(),
					// 	$this->bucket->get_region()->get_identifier(),
					// 	$this->bucket->get_region()->get_display_name(),
					// 	$error_message
					// ));
				}
				return false;
			}
		} catch (\Exception $e) {
			// error_log('S3 Media Sync: Error creating S3 client - ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Try to upload a file using the stream wrapper
	 * 
	 * @param \Aws\S3\S3Client $s3_client The S3 client
	 * @param string $file_path The local file path
	 * @param string $relative_path The relative path (key) in S3
	 * @return bool Whether the upload succeeded
	 */
	protected function try_stream_wrapper_upload($s3_client, $file_path, $relative_path) {
		// error_log('S3 Media Sync: Attempting to upload via stream wrapper: ' . $file_path . ' -> s3://' . $this->bucket->get_name() . '/' . $relative_path);
		
		// Simple ACL handling
		$stream_options = [];
		if (isset($this->settings['use_acl']) && $this->settings['use_acl']) {
			$stream_options['acl'] = isset($this->settings['object_acl']) ? $this->settings['object_acl'] : 'public-read';
		} else {
			$stream_options['acl'] = null;
		}
		
		// Create stream context
		$context = stream_context_create(['s3' => $stream_options]);
		
		// Copy to S3
		$s3_path = 's3://' . $this->bucket->get_name() . '/' . $relative_path;
		$result = @copy($file_path, $s3_path, $context);
		
		if (!$result) {
			$error = error_get_last();
			
			// Handle AccessControlListNotSupported error
			if (isset($this->settings['use_acl']) && $this->settings['use_acl'] && 
				$error && strpos($error['message'], 'AccessControlListNotSupported') !== false) {
				
				// error_log('S3 Media Sync: AccessControlListNotSupported error detected, retrying without ACL');
				
				// Retry without ACL
				$stream_options['acl'] = null;
				$context = stream_context_create(['s3' => $stream_options]);
				$result = @copy($file_path, $s3_path, $context);
				
				// If successful, update settings
				if ($result) {
					// error_log('S3 Media Sync: Successfully copied to S3 without ACL');
					$this->settings['use_acl'] = false;
					update_option('s3_media_sync_settings', $this->settings);
					return true;
				} else {
					$retry_error = error_get_last();
					// error_log('S3 Media Sync: Failed to copy to S3 even without ACL: ' . ($retry_error ? $retry_error['message'] : 'Unknown error'));
					return false;
				}
			} else if ($error) {
				// error_log('S3 Media Sync: Failed to copy to S3 via stream wrapper: ' . $error['message']);
				
				// Check for specific error types to provide more helpful messages
				if (strpos($error['message'], 'Error executing "HeadObject"') !== false) {
					// error_log('S3 Media Sync: This may be a permissions issue. Check your IAM policy and bucket settings.');
				} elseif (strpos($error['message'], 'InvalidAccessKeyId') !== false) {
					// error_log('S3 Media Sync: Invalid AWS access key. Check your credentials.');
				} elseif (strpos($error['message'], 'AccessDenied') !== false) {
					// error_log('S3 Media Sync: Access denied. Check bucket permissions and IAM policy.');
					// error_log('S3 Media Sync: Ensure your IAM policy allows access to this path: ' . $relative_path);
				}
				return false;
			}
			return false;
		} else {
			// error_log('S3 Media Sync: Successfully copied to S3 via stream wrapper');
			return true;
		}
	}
	
	/**
	 * Try to upload a file using direct S3 API calls
	 * 
	 * @param \Aws\S3\S3Client $s3_client The S3 client
	 * @param string $file_path The local file path
	 * @param string $relative_path The relative path (key) in S3
	 * @return bool Whether the upload succeeded
	 */
	protected function try_direct_s3_upload($s3_client, $file_path, $relative_path) {
		// error_log('S3 Media Sync: Attempting to upload via direct API: ' . $file_path . ' -> ' . $this->bucket->get_name() . '/' . $relative_path);
		
		// Log the IAM policy path pattern for debugging
		if (strpos($relative_path, 'wp-content/uploads/') !== 0) {
			// error_log('S3 Media Sync: WARNING - The S3 path does not begin with "wp-content/uploads/" which is required by the IAM policy');
		}
		
		try {
			// Read file contents
			$body = fopen($file_path, 'r');
			if (!$body) {
				// error_log('S3 Media Sync: Could not open file for reading: ' . $file_path);
				return false;
			}
			
			// Get MIME type
			$mime_type = mime_content_type($file_path);
			
			// Prepare params
			$params = [
				'Bucket' => $this->bucket->get_name(),
				'Key' => $relative_path,
				'Body' => $body,
				'ContentType' => $mime_type,
			];
			
			// Add ACL if needed
			if (isset($this->settings['use_acl']) && $this->settings['use_acl']) {
				$params['ACL'] = isset($this->settings['object_acl']) ? $this->settings['object_acl'] : 'public-read';
			}
			
			// error_log('S3 Media Sync: PutObject params - Bucket: ' . $params['Bucket'] . ', Key: ' . $params['Key'] . ', ContentType: ' . $params['ContentType']);
			
			// Upload using putObject
			$result = $s3_client->putObject($params);
			
			// Close the file
			if (is_resource($body)) {
				fclose($body);
			}
			
			// error_log('S3 Media Sync: Successfully uploaded to S3 via direct API');
			
			// Verify the file exists
			try {
				$s3_client->headObject([
					'Bucket' => $this->bucket->get_name(),
					'Key' => $relative_path,
				]);
				// error_log('S3 Media Sync: Successfully verified file exists on S3 after direct upload');
			} catch (\Exception $e) {
				// error_log('S3 Media Sync: Warning - File uploaded but verification failed: ' . $e->getMessage());
			}
			
			return true;
		} catch (\Aws\S3\Exception\S3Exception $e) {
			// error_log('S3 Media Sync: Direct API upload failed: ' . $e->getMessage());
			
			// Additional debug info for permissions errors
			if (strpos($e->getMessage(), 'AccessDenied') !== false) {
				// error_log('S3 Media Sync: Access denied error: Your IAM policy requires paths to start with "wp-content/uploads/"');
				// error_log('S3 Media Sync: Attempted path: ' . $relative_path);
			}
			
			// Handle AccessControlListNotSupported error
			if (strpos($e->getMessage(), 'AccessControlListNotSupported') !== false) {
				// error_log('S3 Media Sync: AccessControlListNotSupported error detected, retrying without ACL');
				
				// Remove ACL and retry
				if (isset($this->settings['use_acl']) && $this->settings['use_acl']) {
					$this->settings['use_acl'] = false;
					update_option('s3_media_sync_settings', $this->settings);
					
					// Try again
					return $this->try_direct_s3_upload($s3_client, $file_path, $relative_path);
				}
			}
			return false;
		} catch (\Exception $e) {
			// error_log('S3 Media Sync: Direct API upload failed with general exception: ' . $e->getMessage());
			return false;
		}
	}
}
