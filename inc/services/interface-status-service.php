<?php
/**
 * Status Service Interface
 *
 * Defines the contract for S3 connection status and configuration operations.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use S3_Media_Sync\Value_Objects\Aws_Identity;
use S3_Media_Sync\Value_Objects\Connection_Status;

/**
 * Interface for checking S3 connection status and retrieving configuration.
 *
 * @since 2.1.0
 */
interface Status_Service_Interface {

	/**
	 * Check the S3 connection status.
	 *
	 * @since 2.1.0
	 *
	 * @return Connection_Status The connection status result.
	 */
	public function get_connection_status(): Connection_Status;

	/**
	 * Get the AWS caller identity.
	 *
	 * @since 2.1.0
	 *
	 * @return Aws_Identity|null The AWS identity, or null if unavailable.
	 */
	public function get_aws_identity(): ?Aws_Identity;

	/**
	 * Get a summary of the current settings for display.
	 *
	 * @since 2.1.0
	 *
	 * @return array<array{Setting: string, Value: string}> Array of setting rows.
	 */
	public function get_settings_summary(): array;

	/**
	 * Check if required settings are configured.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if all required settings are present.
	 */
	public function has_required_settings(): bool;
}
