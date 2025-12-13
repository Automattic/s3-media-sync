<?php
/**
 * AWS Identity Value Object
 *
 * Immutable object representing AWS caller identity information.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing AWS caller identity.
 *
 * @since 2.1.0
 */
class Aws_Identity {

	/**
	 * The AWS account ID.
	 *
	 * @var string
	 */
	private string $account_id;

	/**
	 * The IAM ARN.
	 *
	 * @var string
	 */
	private string $arn;

	/**
	 * The user ID.
	 *
	 * @var string
	 */
	private string $user_id;

	/**
	 * Constructor.
	 *
	 * @param string $account_id The AWS account ID.
	 * @param string $arn        The IAM ARN.
	 * @param string $user_id    The user ID.
	 */
	private function __construct( string $account_id, string $arn, string $user_id ) {
		$this->account_id = $account_id;
		$this->arn        = $arn;
		$this->user_id    = $user_id;
	}

	/**
	 * Create from STS getCallerIdentity response.
	 *
	 * @since 2.1.0
	 *
	 * @param array $response The STS response with Account, Arn, and UserId keys.
	 * @return self
	 */
	public static function from_sts_response( array $response ): self {
		return new self(
			$response['Account'] ?? '',
			$response['Arn'] ?? '',
			$response['UserId'] ?? ''
		);
	}

	/**
	 * Create with explicit values.
	 *
	 * @since 2.1.0
	 *
	 * @param string $account_id The AWS account ID.
	 * @param string $arn        The IAM ARN.
	 * @param string $user_id    The user ID.
	 * @return self
	 */
	public static function create( string $account_id, string $arn, string $user_id = '' ): self {
		return new self( $account_id, $arn, $user_id );
	}

	/**
	 * Get the AWS account ID.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_account_id(): string {
		return $this->account_id;
	}

	/**
	 * Get the IAM ARN.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_arn(): string {
		return $this->arn;
	}

	/**
	 * Get the user ID.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_user_id(): string {
		return $this->user_id;
	}

	/**
	 * Get the IAM entity name from the ARN.
	 *
	 * Extracts the user or role name from the full ARN.
	 * Example: "arn:aws:iam::123456789012:user/MyUser" returns "user/MyUser"
	 *
	 * @since 2.1.0
	 *
	 * @return string The entity name portion of the ARN.
	 */
	public function get_entity_name(): string {
		// ARN format: arn:aws:iam::account-id:entity-type/entity-name.
		$parts = explode( ':', $this->arn );
		$last  = end( $parts );
		return false !== $last ? $last : '';
	}

	/**
	 * Check if the identity is for an IAM user.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_user(): bool {
		return false !== strpos( $this->arn, ':user/' );
	}

	/**
	 * Check if the identity is for an IAM role.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_role(): bool {
		return false !== strpos( $this->arn, ':assumed-role/' ) || false !== strpos( $this->arn, ':role/' );
	}

	/**
	 * Convert to array for display.
	 *
	 * @since 2.1.0
	 *
	 * @return array{account_id: string, arn: string, user_id: string}
	 */
	public function to_array(): array {
		return array(
			'account_id' => $this->account_id,
			'arn'        => $this->arn,
			'user_id'    => $this->user_id,
		);
	}
}
