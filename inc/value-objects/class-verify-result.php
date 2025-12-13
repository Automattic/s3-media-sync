<?php
/**
 * Verify Result Value Object
 *
 * Immutable object representing the result of verifying an attachment against S3.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing a verification result for a single attachment.
 *
 * @since 2.1.0
 */
class Verify_Result {

	/**
	 * Issue type constants.
	 */
	public const ISSUE_MISSING       = 'missing';
	public const ISSUE_SIZE_MISMATCH = 'size_mismatch';
	public const ISSUE_MD5_MISMATCH  = 'md5_mismatch';

	/**
	 * The WordPress attachment ID.
	 *
	 * @var int
	 */
	private int $attachment_id;

	/**
	 * The attachment title.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The type of issue found, or null if no issue.
	 *
	 * @var string|null
	 */
	private ?string $issue_type;

	/**
	 * The local file size in bytes.
	 *
	 * @var int|null
	 */
	private ?int $local_size;

	/**
	 * The S3 file size in bytes.
	 *
	 * @var int|null
	 */
	private ?int $s3_size;

	/**
	 * The local file path.
	 *
	 * @var string|null
	 */
	private ?string $local_path;

	/**
	 * The S3 key.
	 *
	 * @var string|null
	 */
	private ?string $s3_key;

	/**
	 * Whether the issue was fixed.
	 *
	 * @var bool
	 */
	private bool $was_fixed;

	/**
	 * Constructor.
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param string|null $issue_type    The issue type, or null if no issue.
	 * @param int|null    $local_size    The local file size.
	 * @param int|null    $s3_size       The S3 file size.
	 * @param string|null $local_path    The local file path.
	 * @param string|null $s3_key        The S3 key.
	 * @param bool        $was_fixed     Whether the issue was fixed.
	 */
	private function __construct(
		int $attachment_id,
		string $title,
		?string $issue_type,
		?int $local_size,
		?int $s3_size,
		?string $local_path,
		?string $s3_key,
		bool $was_fixed = false
	) {
		$this->attachment_id = $attachment_id;
		$this->title         = $title;
		$this->issue_type    = $issue_type;
		$this->local_size    = $local_size;
		$this->s3_size       = $s3_size;
		$this->local_path    = $local_path;
		$this->s3_key        = $s3_key;
		$this->was_fixed     = $was_fixed;
	}

	/**
	 * Create a result indicating the file is missing from S3.
	 *
	 * @since 2.1.0
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param int         $local_size    The local file size.
	 * @param string|null $local_path    The local file path.
	 * @param string|null $s3_key        The S3 key.
	 * @return self
	 */
	public static function missing(
		int $attachment_id,
		string $title,
		int $local_size,
		?string $local_path = null,
		?string $s3_key = null
	): self {
		return new self(
			$attachment_id,
			$title,
			self::ISSUE_MISSING,
			$local_size,
			null,
			$local_path,
			$s3_key
		);
	}

	/**
	 * Create a result indicating a size mismatch.
	 *
	 * @since 2.1.0
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param int         $local_size    The local file size.
	 * @param int         $s3_size       The S3 file size.
	 * @param string|null $local_path    The local file path.
	 * @param string|null $s3_key        The S3 key.
	 * @return self
	 */
	public static function size_mismatch(
		int $attachment_id,
		string $title,
		int $local_size,
		int $s3_size,
		?string $local_path = null,
		?string $s3_key = null
	): self {
		return new self(
			$attachment_id,
			$title,
			self::ISSUE_SIZE_MISMATCH,
			$local_size,
			$s3_size,
			$local_path,
			$s3_key
		);
	}

	/**
	 * Create a result indicating an MD5 mismatch.
	 *
	 * @since 2.1.0
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param int         $local_size    The local file size.
	 * @param int         $s3_size       The S3 file size.
	 * @param string|null $local_path    The local file path.
	 * @param string|null $s3_key        The S3 key.
	 * @return self
	 */
	public static function md5_mismatch(
		int $attachment_id,
		string $title,
		int $local_size,
		int $s3_size,
		?string $local_path = null,
		?string $s3_key = null
	): self {
		return new self(
			$attachment_id,
			$title,
			self::ISSUE_MD5_MISMATCH,
			$local_size,
			$s3_size,
			$local_path,
			$s3_key
		);
	}

	/**
	 * Create a result indicating no issues (files match).
	 *
	 * @since 2.1.0
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param int         $local_size    The local file size.
	 * @param int         $s3_size       The S3 file size.
	 * @param string|null $local_path    The local file path.
	 * @param string|null $s3_key        The S3 key.
	 * @return self
	 */
	public static function ok(
		int $attachment_id,
		string $title,
		int $local_size,
		int $s3_size,
		?string $local_path = null,
		?string $s3_key = null
	): self {
		return new self(
			$attachment_id,
			$title,
			null,
			$local_size,
			$s3_size,
			$local_path,
			$s3_key
		);
	}

	/**
	 * Get the attachment ID.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_attachment_id(): int {
		return $this->attachment_id;
	}

	/**
	 * Get the attachment title.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Get the issue type.
	 *
	 * @since 2.1.0
	 *
	 * @return string|null The issue type constant, or null if no issue.
	 */
	public function get_issue_type(): ?string {
		return $this->issue_type;
	}

	/**
	 * Get the human-readable issue label.
	 *
	 * @since 2.1.0
	 *
	 * @return string The issue label, or empty string if no issue.
	 */
	public function get_issue_label(): string {
		switch ( $this->issue_type ) {
			case self::ISSUE_MISSING:
				return 'Missing on S3';
			case self::ISSUE_SIZE_MISMATCH:
				return 'Size mismatch';
			case self::ISSUE_MD5_MISMATCH:
				return 'MD5 mismatch';
			default:
				return '';
		}
	}

	/**
	 * Get the local file size.
	 *
	 * @since 2.1.0
	 *
	 * @return int|null
	 */
	public function get_local_size(): ?int {
		return $this->local_size;
	}

	/**
	 * Get the S3 file size.
	 *
	 * @since 2.1.0
	 *
	 * @return int|null
	 */
	public function get_s3_size(): ?int {
		return $this->s3_size;
	}

	/**
	 * Get the local file path.
	 *
	 * @since 2.1.0
	 *
	 * @return string|null
	 */
	public function get_local_path(): ?string {
		return $this->local_path;
	}

	/**
	 * Get the S3 key.
	 *
	 * @since 2.1.0
	 *
	 * @return string|null
	 */
	public function get_s3_key(): ?string {
		return $this->s3_key;
	}

	/**
	 * Check if this result indicates an issue that needs fixing.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function has_issue(): bool {
		return null !== $this->issue_type;
	}

	/**
	 * Check if the issue was fixed.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function was_fixed(): bool {
		return $this->was_fixed;
	}

	/**
	 * Create a new result with the fixed flag set.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $fixed Whether the fix was successful.
	 * @return self New instance with updated fix status.
	 */
	public function with_fixed( bool $fixed ): self {
		return new self(
			$this->attachment_id,
			$this->title,
			$this->issue_type,
			$this->local_size,
			$this->s3_size,
			$this->local_path,
			$this->s3_key,
			$fixed
		);
	}

	/**
	 * Convert to array for table display.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $include_fixed Whether to include the Fixed column.
	 * @return array
	 */
	public function to_table_row( bool $include_fixed = false ): array {
		$row = array(
			'ID'         => $this->attachment_id,
			'Title'      => $this->title,
			'Issue'      => $this->get_issue_label(),
			'Local Size' => null !== $this->local_size ? size_format( $this->local_size, 2 ) : 'N/A',
			'S3 Size'    => null !== $this->s3_size ? size_format( $this->s3_size, 2 ) : 'N/A',
		);

		if ( $include_fixed ) {
			$row['Fixed'] = $this->was_fixed ? 'Yes' : 'No';
		}

		return $row;
	}
}
