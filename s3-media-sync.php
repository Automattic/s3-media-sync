<?php
/**
 * Plugin Name: S3 Media Sync
 * Description: Sync full media backups to S3.
 * Author: Alexis Kulash, WordPress VIP
 * Requires PHP: 8.1
 * Text Domain: s3-media-sync
 * Domain Path: /languages/
 * Version: 1.3.0
 */

/**
 * Class that handles s3 stream wrapper
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/class-s3-media-sync-stream-wrapper.php';

/**
 * Class that handles core plugin functionality
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/class-s3-media-sync.php';

/**
 * Class that adds WP-CLI commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
	require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-wp-cli.php';
}

/**
 * Set up the plugin
 */
function s3_media_sync_setup() {

	// Ensure the AWS SDK can be loaded.
	if ( ! class_exists( '\\Aws\\S3\\S3Client' ) ) {
		// Require AWS Autoloader file.
		require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	}

	$instance = S3_Media_Sync::init();
	$instance->setup();
}
add_action( 'plugins_loaded', 's3_media_sync_setup' );
