<?php
/**
 * Status Command
 *
 * WP-CLI command for checking S3 connection status and configuration.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Cli;

use S3_Media_Sync\Services\Status_Service_Interface;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Check S3 connection status and configuration.
 *
 * @since 2.1.0
 */
class Status_Command {

	/**
	 * The status service.
	 *
	 * @var Status_Service_Interface
	 */
	private Status_Service_Interface $status_service;

	/**
	 * Constructor.
	 *
	 * @param Status_Service_Interface $status_service The status service.
	 */
	public function __construct( Status_Service_Interface $status_service ) {
		$this->status_service = $status_service;
	}

	/**
	 * Get status information about the S3 connection and plugin configuration.
	 *
	 * ## EXAMPLES
	 *
	 *     # Check S3 connection status and configuration
	 *     $ wp s3-media status
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		// Check if required settings are available.
		if ( ! $this->status_service->has_required_settings() ) {
			WP_CLI::error( 'S3 Media Sync is not properly configured. Please set up your AWS credentials in the WordPress admin.' );
			return;
		}

		// Check S3 connection.
		$connection_status = $this->status_service->get_connection_status();

		if ( ! $connection_status->is_connected() ) {
			WP_CLI::error(
				sprintf(
					'Failed to connect to S3: %s',
					$connection_status->get_error_message()
				)
			);
			return;
		}

		WP_CLI::success( 'Successfully connected to S3' );

		// Display configuration information.
		$settings_summary = $this->status_service->get_settings_summary();
		$settings_data    = [];

		foreach ( $settings_summary as $setting => $value ) {
			$settings_data[] = [
				'Setting' => $setting,
				'Value'   => $value,
			];
		}

		Utils\format_items( 'table', $settings_data, [ 'Setting', 'Value' ] );

		// Display AWS identity info if available.
		$identity = $this->status_service->get_aws_identity();

		if ( null !== $identity ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'AWS Account Information:' );
			WP_CLI::line( '- Account ID: ' . $identity->get_account_id() );
			WP_CLI::line( '- IAM User/Role: ' . $identity->get_arn() );
		} else {
			WP_CLI::warning( 'Unable to retrieve AWS identity information.' );
		}
	}
}
