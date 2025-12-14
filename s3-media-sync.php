<?php
/**
 * Plugin Name: S3 Media Sync
 * Description: Sync full media backups to S3.
 * Version: 1.4.1
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Alexis Kulash, WordPress VIP
 * Text Domain: s3-media-sync
 * Domain Path: /languages/
 */

define( 'S3_MEDIA_SYNC_FILE', __FILE__ );
define( 'S3_MEDIA_SYNC_GITHUB_URL', 'https://github.com/automattic/s3-media-sync' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( defined( 'WP_CLI' ) ) {
	// Register individual commands with dependency injection.
	$settings_handler = new S3_Media_Sync_Settings();
	$repository       = null;

	// Only create the repository if settings are configured.
	if ( $settings_handler->has_required_settings() ) {
		$settings       = $settings_handler->get_settings();
		$client_factory = new S3_Media_Sync\S3_Media_Sync_Client_Factory();
		$s3_client      = $client_factory->create( $settings );
		$bucket         = S3_Media_Sync\Value_Objects\S3_Bucket::from_settings( $settings );
		$repository     = new S3_Media_Sync\Services\S3_Repository( $s3_client, $bucket );
	}

	$status_service = new S3_Media_Sync\Services\Status_Service( $settings_handler, $repository );
	$status_command = new S3_Media_Sync\Cli\Status_Command( $status_service );
	WP_CLI::add_command( 's3-media status', $status_command );

	// Commands that require a configured repository.
	if ( null !== $repository ) {
		global $wpdb;

		// Verify command.
		$verify_service = new S3_Media_Sync\Services\Verify_Service( $repository );
		$verify_command = new S3_Media_Sync\Cli\Verify_Command( $verify_service );
		WP_CLI::add_command( 's3-media verify', $verify_command );

		// Cleanup command.
		$cleanup_service = new S3_Media_Sync\Services\Cleanup_Service( $repository, $wpdb );
		$cleanup_command = new S3_Media_Sync\Cli\Cleanup_Command( $cleanup_service );
		WP_CLI::add_command( 's3-media cleanup', $cleanup_command );

		// Sync service for upload commands.
		$sync_service = new S3_Media_Sync\Services\Sync_Service( $repository, $settings, $wpdb );

		// Upload command.
		$upload_command = new S3_Media_Sync\Cli\Upload_Command( $sync_service );
		WP_CLI::add_command( 's3-media upload', $upload_command );

		// Upload-all command.
		$upload_all_command = new S3_Media_Sync\Cli\Upload_All_Command( $sync_service );
		WP_CLI::add_command( 's3-media upload-all', $upload_all_command );

		// Remove command.
		$remove_command = new S3_Media_Sync\Cli\Remove_Command( $repository );
		WP_CLI::add_command( 's3-media rm', $remove_command );
	}
}

// Initialize the plugin with dependencies
add_action(
	'plugins_loaded',
	function() {
		$settings_handler = new S3_Media_Sync_Settings();

		// Only initialize S3 sync if settings are configured.
		if ( $settings_handler->has_required_settings() ) {
			$s3_media_sync = new S3_Media_Sync( $settings_handler );
			$s3_media_sync->setup();
		} else {
			// Register settings page only.
			$settings_handler->init();
		}
	}
);
