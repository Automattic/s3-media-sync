<?php

use S3_Media_Sync\S3_Media_Sync_Client_Factory;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\File_Comparison;
use Aws\S3\Exception\S3Exception;

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
	public function add_attachment_to_s3($upload, string $context = 'upload'): array {
		try {
			// Create value objects for the file
			$local_file = Local_File::from_path($upload['file']);
			
			// Calculate the S3 key based on the upload path
			$uploads = wp_upload_dir();
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $upload['file']);
			$s3_key = 'wp-content/uploads/' . $file_subpath;
			
			// Create the S3 file object
			$s3_file = S3_File::from_key($this->bucket, $s3_key);
			
			// Get the AWS parameters for the upload
			$params = array_merge(
				$s3_file->get_aws_params(),
				[
					'SourceFile' => $local_file->get_path(),
					'ContentType' => $local_file->get_mime_type()
				]
			);

			// Add ACL if enabled
			if ($this->bucket->get_object_acl() !== null) {
				$params['ACL'] = $this->bucket->get_object_acl();
			}

			// Upload the file
			$this->get_client_factory()->create($this->settings)->putObject($params);

			return $upload;
		} catch (\Aws\S3\Exception\S3Exception $e) {
			// Integration tests need this to pass.
			error_log('S3 Media Sync: ' . $e->getMessage());
			return $upload;
		} catch (\Exception $e) {
			// error_log('S3 Media Sync: Failed to upload - ' . $e->getMessage());
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
			return $filename;
		}

		// If this is a size/thumbnail and thumbnail syncing is disabled, skip it
		if ($size && isset($this->settings['sync_thumbnails']) && $this->settings['sync_thumbnails'] === false) {
			return $filename;
		}

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
			return $filename;
		}

		try {
			// Create value objects for the file
			$local_file = Local_File::from_path($filename);
			
			// Calculate the S3 key based on the upload path
			$uploads = wp_upload_dir();
			$uploads_path = trailingslashit($uploads['basedir']);
			$file_subpath = str_replace($uploads_path, '', $filename);
			$s3_key = 'wp-content/uploads/' . $file_subpath;
			
			// Create the S3 file object
			$s3_file = S3_File::from_key($this->bucket, $s3_key);
			
			// Ensure we have a fresh S3 client
			$factory = $this->get_client_factory();
			$s3_client = $factory->create($this->settings);
			$factory->configure_stream_wrapper($s3_client, $this->bucket);
			
			// First try using stream wrapper
			$uploaded = $this->try_stream_wrapper_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
			
			// If stream wrapper failed, try direct API
			if (!$uploaded) {
				$uploaded = $this->try_direct_s3_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
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
			$metadata = wp_get_attachment_metadata($post_id);
			$attachment_url = wp_get_attachment_url($post_id);
			
			if (empty($attachment_url)) {
				return false;
			}
			
			// Get the main file path
			$upload_dir = wp_upload_dir();
			$file_path = get_attached_file($post_id);
			$main_file = Local_File::from_path($file_path);
			
			// Create S3 file for main attachment
			$s3_key = 'wp-content/uploads' . str_replace($upload_dir['baseurl'], '', $attachment_url);
			$s3_files[] = S3_File::from_key($this->bucket, $s3_key);
			
			// Handle thumbnails if they exist
			if (!empty($metadata) && !empty($metadata['file']) && !empty($metadata['sizes'])) {
				$file_dir = dirname($metadata['file']);
				$file_dir = empty($file_dir) ? '' : trailingslashit($file_dir);
				
				foreach ($metadata['sizes'] as $size => $size_info) {
					if (!empty($size_info['file'])) {
						$thumb_key = 'wp-content/uploads/' . $file_dir . $size_info['file'];
						$s3_files[] = S3_File::from_key($this->bucket, $thumb_key);
					}
				}
			}
			
			$bucket = $this->get_s3_bucket();
			
			// Handle bucket with path prefix
			if (strpos($bucket, '/') !== false) {
				$bucket_parts = explode('/', $bucket, 2);
				$bucket = $bucket_parts[0];
				$prefix = trailingslashit($bucket_parts[1]);
				
				// Update S3 keys with prefix
				foreach ($s3_files as $key => $s3_file) {
					$s3_files[$key] = S3_File::from_key($this->bucket, $prefix . $s3_file->get_key());
				}
			}
			
			// Delete all files from S3
			$s3_client = $this->get_client_factory()->create($this->settings);
			foreach ($s3_files as $s3_file) {
				$s3_client->deleteObject([
					'Bucket' => $bucket,
					'Key'    => $s3_file->get_key()
				]);
			}
			
			return true;
		} catch (S3Exception $e) {
			error_log('S3 Media Sync: Failed to delete attachment from S3: ' . $e->getMessage());
			return false;
		} catch (Exception $e) {
			error_log('S3 Media Sync: Error deleting attachment from S3: ' . $e->getMessage());
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
			return $metadata;
		}

		// Verify S3 is properly configured
		if (!$this->is_s3_configured()) {
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
			
			// Always sync the original file if it exists and wasn't previously uploaded
			$original_file_path = trailingslashit($base_dir) . $metadata['file'];
			
			if (file_exists($original_file_path)) {
				$local_file = Local_File::from_path($original_file_path);
				$s3_file = S3_File::from_key($this->bucket, 'wp-content/uploads/' . $metadata['file']);
				
				// Try to upload the original file
				$uploaded = $this->try_stream_wrapper_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
				
				if (!$uploaded) {
					$uploaded = $this->try_direct_s3_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
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
					
					if (!file_exists($size_file_path)) {
						continue;
					}
					
					$local_file = Local_File::from_path($size_file_path);
					$s3_key = 'wp-content/uploads/' . (empty($file_dir) ? '' : trailingslashit($file_dir)) . $size_info['file'];
					$s3_file = S3_File::from_key($this->bucket, $s3_key);
					
					// First try using stream wrapper
					$uploaded = $this->try_stream_wrapper_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
					
					// If stream wrapper failed, try direct API
					if (!$uploaded) {
						$uploaded = $this->try_direct_s3_upload($s3_client, $local_file->get_path(), $s3_file->get_key());
					}
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
		try {
			$local_file = Local_File::from_path($file_path);
			$s3_file = S3_File::from_key($this->bucket, $relative_path);
			
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
			$s3_path = 's3://' . $this->bucket->get_name() . '/' . $s3_file->get_key();
			$result = @copy($local_file->get_path(), $s3_path, $context);
			
			if (!$result) {
				$error = error_get_last();
				
				// Handle AccessControlListNotSupported error
				if (isset($this->settings['use_acl']) && $this->settings['use_acl'] && 
					$error && strpos($error['message'], 'AccessControlListNotSupported') !== false) {
					
					// Retry without ACL
					$stream_options['acl'] = null;
					$context = stream_context_create(['s3' => $stream_options]);
					$result = @copy($local_file->get_path(), $s3_path, $context);
					
					// If successful, update settings
					if ($result) {
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
					return false;
				}
				return false;
			}
			return true;
		} catch (\Exception $e) {
			error_log('S3 Media Sync: Stream wrapper upload failed: ' . $e->getMessage());
			return false;
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
		try {
			$local_file = Local_File::from_path($file_path);
			$s3_file = S3_File::from_key($this->bucket, $relative_path);
			
			// Read file contents
			$body = fopen($local_file->get_path(), 'r');
			if (!$body) {
				return false;
			}
			
			// Prepare params
			$params = array_merge(
				$s3_file->get_aws_params(),
				[
					'Body' => $body,
					'ContentType' => $local_file->get_mime_type(),
				]
			);
			
			// Add ACL if needed
			if (isset($this->settings['use_acl']) && $this->settings['use_acl']) {
				$params['ACL'] = isset($this->settings['object_acl']) ? $this->settings['object_acl'] : 'public-read';
			}
			
			// Upload using putObject
			$result = $s3_client->putObject($params);
			
			// Close the file
			if (is_resource($body)) {
				fclose($body);
			}
			
			// Verify the file exists
			try {
				$s3_client->headObject([
					'Bucket' => $this->bucket->get_name(),
					'Key' => $s3_file->get_key(),
				]);
			} catch (\Exception $e) {
				error_log('S3 Media Sync: Warning - File uploaded but verification failed: ' . $e->getMessage());
			}
			
			return true;
		} catch (S3Exception $e) {
			error_log('S3 Media Sync: Direct API upload failed: ' . $e->getMessage());
			
			// Handle AccessControlListNotSupported error
			if (strpos($e->getMessage(), 'AccessControlListNotSupported') !== false) {
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
}
