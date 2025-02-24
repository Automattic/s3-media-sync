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

define( 'S3_MEDIA_SYNC_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-settings.php';
require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync.php';
require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-stream-wrapper.php';
require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-client-factory.php';

if ( defined( 'WP_CLI' ) && class_exists( 'WPCOM_VIP_CLI_Command' ) ) {
	require_once dirname( __FILE__ ) . '/inc/class-s3-media-sync-wp-cli.php';
}

// Initialize the plugin with dependencies
add_action(
	'plugins_loaded',
	function() {
		$settings_handler = new S3_Media_Sync_Settings();
		$s3_media_sync = new S3_Media_Sync( $settings_handler );
		$s3_media_sync->setup();
	}
);
