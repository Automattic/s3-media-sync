<?php
/**
 * Status Service Implementation
 *
 * Provides S3 connection status and configuration information.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Services;

use Aws\Sts\StsClient;
use S3_Media_Sync\Value_Objects\Aws_Identity;
use S3_Media_Sync\Value_Objects\Connection_Status;

/**
 * Status Service implementation.
 *
 * @since 2.1.0
 */
class Status_Service implements Status_Service_Interface {

	/**
	 * The S3 repository.
	 *
	 * @var S3_Repository_Interface
	 */
	private S3_Repository_Interface $repository;

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param S3_Repository_Interface $repository The S3 repository.
	 * @param array                   $settings   The plugin settings array.
	 */
	public function __construct( S3_Repository_Interface $repository, array $settings ) {
		$this->repository = $repository;
		$this->settings   = $settings;
	}

	/**
	 * Check the S3 connection status.
	 *
	 * @since 2.1.0
	 *
	 * @return Connection_Status The connection status result.
	 */
	public function get_connection_status(): Connection_Status {
		if ( ! $this->has_required_settings() ) {
			return Connection_Status::not_configured(
				'S3 Media Sync is not properly configured. Please set up your AWS credentials.'
			);
		}

		$bucket      = $this->repository->get_bucket();
		$bucket_name = $bucket->get_name();
		$region      = $bucket->get_region()->get_identifier();

		try {
			$connected = $this->repository->head_bucket();

			if ( $connected ) {
				return Connection_Status::connected( $bucket_name, $region );
			}

			return Connection_Status::failed(
				$bucket_name,
				$region,
				'Unable to access the S3 bucket.'
			);
		} catch ( \Exception $e ) {
			return Connection_Status::failed(
				$bucket_name,
				$region,
				$e->getMessage()
			);
		}
	}

	/**
	 * Get the AWS caller identity.
	 *
	 * @since 2.1.0
	 *
	 * @return Aws_Identity|null The AWS identity, or null if unavailable.
	 */
	public function get_aws_identity(): ?Aws_Identity {
		if ( ! $this->has_required_settings() ) {
			return null;
		}

		try {
			$sts_client = $this->create_sts_client();
			$response   = $sts_client->getCallerIdentity();

			return Aws_Identity::from_sts_response( $response->toArray() );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Get a summary of the current settings for display.
	 *
	 * @since 2.1.0
	 *
	 * @return array<array{Setting: string, Value: string}> Array of setting rows.
	 */
	public function get_settings_summary(): array {
		$bucket = $this->repository->get_bucket();

		$summary = array();

		$summary[] = array(
			'Setting' => 'Bucket',
			'Value'   => $this->get_full_bucket_path(),
		);

		$summary[] = array(
			'Setting' => 'Region',
			'Value'   => $this->settings['region'] ?? '',
		);

		$summary[] = array(
			'Setting' => 'Use ACLs',
			'Value'   => $this->format_boolean( $this->settings['use_acl'] ?? false ),
		);

		$summary[] = array(
			'Setting' => 'Object ACL',
			'Value'   => $this->settings['object_acl'] ?? 'Not set',
		);

		$summary[] = array(
			'Setting' => 'Sync Thumbnails',
			'Value'   => $this->format_boolean( $this->settings['sync_thumbnails'] ?? true ),
		);

		return $summary;
	}

	/**
	 * Check if required settings are configured.
	 *
	 * @since 2.1.0
	 *
	 * @return bool True if all required settings are present.
	 */
	public function has_required_settings(): bool {
		$required = array( 'bucket', 'region' );

		foreach ( $required as $key ) {
			if ( empty( $this->settings[ $key ] ) ) {
				return false;
			}
		}

		// Either credentials or IAM role must be available.
		$has_credentials = ! empty( $this->settings['key'] ) && ! empty( $this->settings['secret'] );
		$has_iam_role    = defined( 'S3_MEDIA_SYNC_USE_IAM_ROLE' ) && S3_MEDIA_SYNC_USE_IAM_ROLE;

		return $has_credentials || $has_iam_role;
	}

	/**
	 * Get the full bucket path including prefix.
	 *
	 * @since 2.1.0
	 *
	 * @return string The full bucket path.
	 */
	private function get_full_bucket_path(): string {
		$bucket = $this->repository->get_bucket();
		$name   = $bucket->get_name();
		$prefix = $bucket->get_prefix();

		if ( ! empty( $prefix ) ) {
			return $name . '/' . $prefix;
		}

		return $name;
	}

	/**
	 * Format a boolean value for display.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value The value to format.
	 * @return string "Yes" or "No".
	 */
	private function format_boolean( $value ): string {
		return $value ? 'Yes' : 'No';
	}

	/**
	 * Create an STS client for identity checks.
	 *
	 * @since 2.1.0
	 *
	 * @return StsClient The STS client.
	 */
	private function create_sts_client(): StsClient {
		$config = array(
			'region'  => $this->settings['region'],
			'version' => 'latest',
		);

		// Only add credentials if not using IAM role.
		if ( ! empty( $this->settings['key'] ) && ! empty( $this->settings['secret'] ) ) {
			$config['credentials'] = array(
				'key'    => $this->settings['key'],
				'secret' => $this->settings['secret'],
			);
		}

		return new StsClient( $config );
	}
}
