<?php
/**
 * Plugin Name: S3 Media Sync
 * Description: Sync full media backups to S3.
 * Author: Alexis Kulash, WordPress VIP
 * Requires PHP: 8.1
 * Text Domain: s3-media-sync
 * Domain Path: /languages/
 * Version: 1.4.1
 */

// Define plugin constants
define( 'S3_MEDIA_SYNC_FILE', __FILE__ );

// Load required files
require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-settings.php';
require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync.php';

/**
 * Class that handles s3 stream wrapper
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/class-s3-media-sync-stream-wrapper.php';

/**
 * Class that adds WP-CLI commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
	require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-wp-cli.php';
}

// Initialize the plugin
add_action( 'plugins_loaded', [ S3_Media_Sync::init(), 'setup' ] );
