<?php

class S3_Media_Sync {
	private $settings;
	private $settings_handler;
	private $s3_client;

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

			// Hook into WordPress media handling
			// These hooks match the original plugin behavior and test expectations
			add_filter( 'wp_handle_upload', [ $this, 'add_attachment_to_s3' ], 10, 2 );
			add_action( 'delete_attachment', [ $this, 'delete_attachment_from_s3' ], 10, 1 );
			
			// For image editor saves (cropping, etc.)
			add_filter( 'wp_save_image_editor_file', [ $this, 'add_updated_attachment_to_s3' ], 10, 5 );
			
			// For image sizes and thumbnails
			add_filter( 'wp_update_attachment_metadata', [ $this, 'sync_attachment_metadata' ], 10, 2 );
			
			// Debug log to confirm hook registration
			error_log('S3 Media Sync: Hooks registered for uploads, deletion, image editing, and thumbnail generation');
		} else {
			error_log('S3 Media Sync: Required settings missing - hooks not registered');
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
	public function add_attachment_to_s3( $upload, $context ) {
		// Skip if upload is not an array or doesn't have expected structure
		if (!is_array($upload) || empty($upload['file'])) {
			error_log('S3 Media Sync: Upload data is not properly formatted');
			return $upload;
		}

		// Check if file exists
		if (!file_exists($upload['file'])) {
			error_log('S3 Media Sync: File does not exist: ' . $upload['file']);
			return $upload;
		}

		// Get file info for logging
		$file_size = filesize($upload['file']);
		$file_mime = mime_content_type($upload['file']);
		error_log('S3 Media Sync: Processing upload - File: ' . $upload['file'] . ', Size: ' . $file_size . ' bytes, MIME: ' . $file_mime);

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			error_log('S3 Media Sync: S3 is not properly configured, skipping upload');
			return $upload;
		}

		try {
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->settings);

			// Get upload info
			$uploads = wp_upload_dir();
			
			// Create the relative path within the bucket ensuring it has the wp-content/uploads prefix
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $upload['file']);
			$relative_path = 'wp-content/uploads/' . $file_subpath;
			
			error_log('S3 Media Sync: Using S3 path: ' . $relative_path . ' to match IAM policy restrictions');
			
			// First try using stream wrapper (the copy method)
			$this->try_stream_wrapper_upload($s3_client, $upload['file'], $relative_path);
			
			// Verify upload using direct API call
			try {
				$result = $s3_client->headObject([
					'Bucket' => $this->settings['bucket'],
					'Key' => $relative_path,
				]);
				error_log('S3 Media Sync: Verified file exists on S3 via API');
				// Return early if verified
				return $upload;
			} catch (\Aws\S3\Exception\S3Exception $e) {
				error_log('S3 Media Sync: HeadObject check failed: ' . $e->getMessage());
				
				// If the stream wrapper method failed to upload, try direct API method
				$this->try_direct_s3_upload($s3_client, $upload['file'], $relative_path);
			}
		} catch (\Exception $e) {
			error_log('S3 Media Sync upload exception: ' . $e->getMessage());
		}
		
		return $upload;
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
		error_log('S3 Media Sync: Attempting to upload via stream wrapper: ' . $file_path . ' -> s3://' . $this->settings['bucket'] . '/' . $relative_path);
		
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
		$s3_path = 's3://' . $this->settings['bucket'] . '/' . $relative_path;
		$result = @copy($file_path, $s3_path, $context);
		
		if (!$result) {
			$error = error_get_last();
			
			// Handle AccessControlListNotSupported error
			if (isset($this->settings['use_acl']) && $this->settings['use_acl'] && 
				$error && strpos($error['message'], 'AccessControlListNotSupported') !== false) {
				
				error_log('S3 Media Sync: AccessControlListNotSupported error detected, retrying without ACL');
				
				// Retry without ACL
				$stream_options['acl'] = null;
				$context = stream_context_create(['s3' => $stream_options]);
				$result = @copy($file_path, $s3_path, $context);
				
				// If successful, update settings
				if ($result) {
					error_log('S3 Media Sync: Successfully copied to S3 without ACL');
					$this->settings['use_acl'] = false;
					update_option('s3_media_sync_settings', $this->settings);
					return true;
				} else {
					$retry_error = error_get_last();
					error_log('S3 Media Sync: Failed to copy to S3 even without ACL: ' . ($retry_error ? $retry_error['message'] : 'Unknown error'));
					return false;
				}
			} else if ($error) {
				error_log('S3 Media Sync: Failed to copy to S3 via stream wrapper: ' . $error['message']);
				
				// Check for specific error types to provide more helpful messages
				if (strpos($error['message'], 'Error executing "HeadObject"') !== false) {
					error_log('S3 Media Sync: This may be a permissions issue. Check your IAM policy and bucket settings.');
				} elseif (strpos($error['message'], 'InvalidAccessKeyId') !== false) {
					error_log('S3 Media Sync: Invalid AWS access key. Check your credentials.');
				} elseif (strpos($error['message'], 'AccessDenied') !== false) {
					error_log('S3 Media Sync: Access denied. Check bucket permissions and IAM policy.');
					error_log('S3 Media Sync: Ensure your IAM policy allows access to this path: ' . $relative_path);
				}
				return false;
			}
			return false;
		} else {
			error_log('S3 Media Sync: Successfully copied to S3 via stream wrapper');
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
		error_log('S3 Media Sync: Attempting to upload via direct API: ' . $file_path . ' -> ' . $this->settings['bucket'] . '/' . $relative_path);
		
		// Log the IAM policy path pattern for debugging
		if (strpos($relative_path, 'wp-content/uploads/') !== 0) {
			error_log('S3 Media Sync: WARNING - The S3 path does not begin with "wp-content/uploads/" which is required by the IAM policy');
		}
		
		try {
			// Read file contents
			$body = fopen($file_path, 'r');
			if (!$body) {
				error_log('S3 Media Sync: Could not open file for reading: ' . $file_path);
				return false;
			}
			
			// Get MIME type
			$mime_type = mime_content_type($file_path);
			
			// Prepare params
			$params = [
				'Bucket' => $this->settings['bucket'],
				'Key' => $relative_path,
				'Body' => $body,
				'ContentType' => $mime_type,
			];
			
			// Add ACL if needed
			if (isset($this->settings['use_acl']) && $this->settings['use_acl']) {
				$params['ACL'] = isset($this->settings['object_acl']) ? $this->settings['object_acl'] : 'public-read';
			}
			
			error_log('S3 Media Sync: PutObject params - Bucket: ' . $params['Bucket'] . ', Key: ' . $params['Key'] . ', ContentType: ' . $params['ContentType']);
			
			// Upload using putObject
			$result = $s3_client->putObject($params);
			
			// Close the file
			if (is_resource($body)) {
				fclose($body);
			}
			
			error_log('S3 Media Sync: Successfully uploaded to S3 via direct API');
			
			// Verify the file exists
			try {
				$s3_client->headObject([
					'Bucket' => $this->settings['bucket'],
					'Key' => $relative_path,
				]);
				error_log('S3 Media Sync: Successfully verified file exists on S3 after direct upload');
			} catch (\Exception $e) {
				error_log('S3 Media Sync: Warning - File uploaded but verification failed: ' . $e->getMessage());
			}
			
			return true;
		} catch (\Aws\S3\Exception\S3Exception $e) {
			error_log('S3 Media Sync: Direct API upload failed: ' . $e->getMessage());
			
			// Additional debug info for permissions errors
			if (strpos($e->getMessage(), 'AccessDenied') !== false) {
				error_log('S3 Media Sync: Access denied error: Your IAM policy requires paths to start with "wp-content/uploads/"');
				error_log('S3 Media Sync: Attempted path: ' . $relative_path);
			}
			
			// Handle AccessControlListNotSupported error
			if (strpos($e->getMessage(), 'AccessControlListNotSupported') !== false) {
				error_log('S3 Media Sync: AccessControlListNotSupported error detected, retrying without ACL');
				
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
			error_log('S3 Media Sync: Direct API upload failed with general exception: ' . $e->getMessage());
			return false;
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
			error_log('S3 Media Sync: Image editor file does not exist: ' . $filename);
			return $filename;
		}

		// Get file info for logging
		$file_size = filesize($filename);
		error_log('S3 Media Sync: Processing image editor file - File: ' . $filename . ', Size: ' . $file_size . ' bytes, MIME: ' . $mime_type . ', Post ID: ' . $post_id);

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			error_log('S3 Media Sync: S3 is not properly configured, skipping image editor upload');
			return $filename;
		}

		try {
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->settings);
			
			// Get uploads directory info
			$uploads = wp_upload_dir();
			
			// Create the relative path within the bucket ensuring it has the wp-content/uploads prefix
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $filename);
			$relative_path = 'wp-content/uploads/' . $file_subpath;
			
			error_log('S3 Media Sync: Processing edited image: ' . $filename . ' -> S3:' . $relative_path);
			
			// First try using stream wrapper
			$uploaded = $this->try_stream_wrapper_upload($s3_client, $filename, $relative_path);
			
			// If stream wrapper failed, try direct API
			if (!$uploaded) {
				error_log('S3 Media Sync: Stream wrapper upload failed for edited image, trying direct API');
				$uploaded = $this->try_direct_s3_upload($s3_client, $filename, $relative_path);
			}
			
			if ($uploaded) {
				error_log('S3 Media Sync: Successfully uploaded edited image to S3');
			} else {
				error_log('S3 Media Sync: Failed to upload edited image to S3 after all attempts');
			}
		} catch (\Exception $e) {
			error_log('S3 Media Sync image editor exception: ' . $e->getMessage());
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
			$path = 'wp-content/uploads' . $relative_path;
			$bucket = $this->get_s3_bucket();
			
			error_log('S3 Media Sync: Deleting attachment from S3: ' . $path);
			
			// Handle bucket with path prefix
			if (strpos($bucket, '/') !== false) {
				$bucket_parts = explode('/', $bucket, 2);
				$bucket = $bucket_parts[0];
				$prefix = trailingslashit($bucket_parts[1]);
				$path = $prefix . $path;
			}
			
			if (empty($path)) {
				return false;
			}
			
			// List objects with matching prefix to delete
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			
			$objects = $s3_client->listObjects([
				'Bucket' => $bucket,
				'Prefix' => $path
			]);
			
			if (!isset($objects['Contents']) || empty($objects['Contents'])) {
				error_log('S3 Media Sync: No objects found to delete for path: ' . $path);
				return true; // Nothing to delete
			}
			
			// Prepare objects for deletion
			$delete_objects = [];
			foreach ($objects['Contents'] as $object) {
				$delete_objects[] = ['Key' => $object['Key']];
				error_log('S3 Media Sync: Adding object for deletion: ' . $object['Key']);
			}
			
			// Delete the objects
			$result = $s3_client->deleteObjects([
				'Bucket' => $bucket,
				'Delete' => [
					'Objects' => $delete_objects
				]
			]);
			
			error_log('S3 Media Sync: Successfully deleted ' . count($delete_objects) . ' objects from S3');
			return true;
		} catch (\Exception $e) {
			error_log('S3 Media Sync delete error: ' . $e->getMessage());
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

		// Skip if there are no sizes to process
		if (empty($metadata['sizes']) || !is_array($metadata['sizes'])) {
			return $metadata;
		}

		// Skip if the main file path is missing
		if (empty($metadata['file'])) {
			error_log('S3 Media Sync: No file path in metadata for attachment ID ' . $attachment_id);
			return $metadata;
		}

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			error_log('S3 Media Sync: S3 is not properly configured, skipping metadata sync');
			return $metadata;
		}

		try {
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->settings);
			
			$wp_uploads = wp_upload_dir();
			$base_dir = $wp_uploads['basedir'];
			$file_dir = dirname($metadata['file']);
			
			error_log('S3 Media Sync: Processing attachment metadata for ID ' . $attachment_id . ' with ' . count($metadata['sizes']) . ' sizes');
			
			// Also sync the original file if it exists and wasn't previously uploaded
			$original_file_path = trailingslashit($base_dir) . $metadata['file'];
			
			if (file_exists($original_file_path)) {
				$original_s3_key = 'wp-content/uploads/' . $metadata['file'];
				error_log('S3 Media Sync: Processing original file: ' . $original_file_path . ' -> S3:' . $original_s3_key);
				
				// Try to upload the original file
				$uploaded = $this->try_stream_wrapper_upload($s3_client, $original_file_path, $original_s3_key);
				
				if (!$uploaded) {
					error_log('S3 Media Sync: Stream wrapper upload failed for original file, trying direct API');
					$uploaded = $this->try_direct_s3_upload($s3_client, $original_file_path, $original_s3_key);
				}
				
				if ($uploaded) {
					error_log('S3 Media Sync: Successfully uploaded original file to S3');
				} else {
					error_log('S3 Media Sync: Failed to upload original file to S3 after all attempts');
				}
			}
			
			// Process each size
			foreach ($metadata['sizes'] as $size => $size_info) {
				if (empty($size_info['file'])) {
					continue;
				}
				
				// Construct source and destination paths
				$size_file_path = trailingslashit($base_dir) . (empty($file_dir) ? '' : trailingslashit($file_dir)) . $size_info['file'];
				$size_s3_key = 'wp-content/uploads/' . (empty($file_dir) ? '' : trailingslashit($file_dir)) . $size_info['file'];
				
				if (!file_exists($size_file_path)) {
					error_log("S3 Media Sync: Size file doesn't exist: " . $size_file_path);
					continue;
				}
				
				error_log('S3 Media Sync: Processing size ' . $size . ': ' . $size_file_path . ' -> S3:' . $size_s3_key);
				
				// First try using stream wrapper
				$uploaded = $this->try_stream_wrapper_upload($s3_client, $size_file_path, $size_s3_key);
				
				// If stream wrapper failed, try direct API
				if (!$uploaded) {
					error_log('S3 Media Sync: Stream wrapper upload failed for size ' . $size . ', trying direct API');
					$uploaded = $this->try_direct_s3_upload($s3_client, $size_file_path, $size_s3_key);
				}
				
				if ($uploaded) {
					error_log('S3 Media Sync: Successfully uploaded size ' . $size . ' to S3');
				} else {
					error_log('S3 Media Sync: Failed to upload size ' . $size . ' to S3 after all attempts');
				}
			}
		} catch (\Exception $e) {
			error_log('S3 Media Sync metadata sync exception: ' . $e->getMessage());
		}
		
		return $metadata;
	}

	/**
	 * Check if the S3 client and stream wrapper are properly configured
	 *
	 * @return bool Whether the S3 client is properly configured
	 */
	protected function is_s3_configured() {
		// Check if required settings are available
		if (!$this->settings_handler->has_required_settings()) {
			error_log('S3 Media Sync: Required settings are missing');
			return false;
		}
		
		// Check if region is set
		if (!isset($this->settings['region']) || empty($this->settings['region'])) {
			error_log('S3 Media Sync: Region is not set');
			return false;
		}
		
		// Check if bucket is set
		if (!isset($this->settings['bucket']) || empty($this->settings['bucket'])) {
			error_log('S3 Media Sync: Bucket is not set');
			return false;
		}
		
		try {
			// Create a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			
			// Try to configure the stream wrapper
			$factory->configure_stream_wrapper($s3_client, $this->settings);
			
			// Check if stream wrapper is registered
			if (!in_array('s3', stream_get_wrappers())) {
				error_log('S3 Media Sync: S3 stream wrapper is not registered, will use direct API only');
				// We can continue without stream wrapper, as we'll use direct API
			}
			
			// Verify bucket exists and is accessible via direct API call
			try {
				$s3_client->headBucket([
					'Bucket' => $this->settings['bucket']
				]);
				error_log('S3 Media Sync: S3 bucket verified via API: ' . $this->settings['bucket']);
				return true;
			} catch (\Aws\S3\Exception\S3Exception $e) {
				error_log('S3 Media Sync: S3 bucket check failed: ' . $e->getMessage());
				
				// Check for specific errors
				if (strpos($e->getMessage(), 'InvalidAccessKeyId') !== false) {
					error_log('S3 Media Sync: Invalid AWS credentials. Please check your access key and secret key.');
					return false;
				}
				
				if (strpos($e->getMessage(), 'NoSuchBucket') !== false) {
					error_log('S3 Media Sync: Bucket does not exist: ' . $this->settings['bucket']);
					return false;
				}
				
				if (strpos($e->getMessage(), 'AccessDenied') !== false) {
					error_log('S3 Media Sync: Access denied to bucket: ' . $this->settings['bucket'] . '. Check your IAM permissions.');
					error_log('S3 Media Sync: Your IAM user needs s3:ListBucket, s3:GetObject, s3:PutObject, s3:DeleteObject permissions.');
					return false;
				}
				
				// Try a GetBucketLocation call as an alternative check
				try {
					$s3_client->getBucketLocation([
						'Bucket' => $this->settings['bucket']
					]);
					error_log('S3 Media Sync: S3 bucket location verified: ' . $this->settings['bucket']);
					return true;
				} catch (\Aws\S3\Exception\S3Exception $e2) {
					error_log('S3 Media Sync: S3 bucket location check also failed: ' . $e2->getMessage());
					return false;
				}
			}
		} catch (\Exception $e) {
			error_log('S3 Media Sync: S3 configuration check failed with exception: ' . $e->getMessage());
			return false;
		}
		
		return false; // Default to false if we reach here
	}
}
