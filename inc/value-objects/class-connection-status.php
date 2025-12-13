<?php
/**
 * Connection Status Value Object
 *
 * Immutable object representing the S3 connection status.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing S3 connection status.
 *
 * @since 2.1.0
 */
class Connection_Status {

	/**
	 * Whether the connection is successful.
	 *
	 * @var bool
	 */
	private bool $connected;

	/**
	 * The bucket name.
	 *
	 * @var string
	 */
	private string $bucket;

	/**
	 * The region.
	 *
	 * @var string
	 */
	private string $region;

	/**
	 * Error message if connection failed.
	 *
	 * @var string|null
	 */
	private ?string $error;

	/**
	 * Constructor.
	 *
	 * @param bool        $connected Whether connected.
	 * @param string      $bucket    The bucket name.
	 * @param string      $region    The region.
	 * @param string|null $error     Error message if failed.
	 */
	private function __construct( bool $connected, string $bucket, string $region, ?string $error = null ) {
		$this->connected = $connected;
		$this->bucket    = $bucket;
		$this->region    = $region;
		$this->error     = $error;
	}

	/**
	 * Create a successful connection status.
	 *
	 * @since 2.1.0
	 *
	 * @param string $bucket The bucket name.
	 * @param string $region The region.
	 * @return self
	 */
	public static function connected( string $bucket, string $region ): self {
		return new self( true, $bucket, $region, null );
	}

	/**
	 * Create a failed connection status.
	 *
	 * @since 2.1.0
	 *
	 * @param string $bucket The bucket name.
	 * @param string $region The region.
	 * @param string $error  The error message.
	 * @return self
	 */
	public static function failed( string $bucket, string $region, string $error ): self {
		return new self( false, $bucket, $region, $error );
	}

	/**
	 * Create a status indicating missing configuration.
	 *
	 * @since 2.1.0
	 *
	 * @param string $message The error message.
	 * @return self
	 */
	public static function not_configured( string $message = 'S3 Media Sync is not properly configured.' ): self {
		return new self( false, '', '', $message );
	}

	/**
	 * Check if connected.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_connected(): bool {
		return $this->connected;
	}

	/**
	 * Get the bucket name.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_bucket(): string {
		return $this->bucket;
	}

	/**
	 * Get the region.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_region(): string {
		return $this->region;
	}

	/**
	 * Get the error message.
	 *
	 * @since 2.1.0
	 *
	 * @return string|null
	 */
	public function get_error(): ?string {
		return $this->error;
	}

	/**
	 * Check if there is an error.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function has_error(): bool {
		return null !== $this->error;
	}
}
