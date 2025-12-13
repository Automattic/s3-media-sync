<?php
/**
 * Sync Result Value Object
 *
 * Immutable object representing the result of a sync operation.
 *
 * @package S3_Media_Sync
 * @since 2.1.0
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing a sync operation result.
 *
 * @since 2.1.0
 */
class Sync_Result {

	/**
	 * Operation type constants.
	 */
	public const OPERATION_UPLOAD = 'upload';
	public const OPERATION_DELETE = 'delete';

	/**
	 * The attachment ID.
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
	 * Whether the operation was successful.
	 *
	 * @var bool
	 */
	private bool $success;

	/**
	 * The operation type.
	 *
	 * @var string
	 */
	private string $operation;

	/**
	 * Number of files synced.
	 *
	 * @var int
	 */
	private int $files_synced;

	/**
	 * Number of files that failed.
	 *
	 * @var int
	 */
	private int $files_failed;

	/**
	 * Error message if operation failed.
	 *
	 * @var string|null
	 */
	private ?string $error;

	/**
	 * List of file paths that were synced.
	 *
	 * @var string[]
	 */
	private array $synced_paths;

	/**
	 * List of file paths that failed with error messages.
	 *
	 * @var array<string, string>
	 */
	private array $failed_paths;

	/**
	 * Constructor.
	 *
	 * @param int         $attachment_id The attachment ID.
	 * @param string      $title         The attachment title.
	 * @param bool        $success       Whether successful.
	 * @param string      $operation     The operation type.
	 * @param int         $files_synced  Number of files synced.
	 * @param int         $files_failed  Number of files that failed.
	 * @param string|null $error         Error message.
	 * @param string[]    $synced_paths  Synced file paths.
	 * @param array       $failed_paths  Failed file paths with errors.
	 */
	private function __construct(
		int $attachment_id,
		string $title,
		bool $success,
		string $operation,
		int $files_synced,
		int $files_failed,
		?string $error,
		array $synced_paths,
		array $failed_paths
	) {
		$this->attachment_id = $attachment_id;
		$this->title         = $title;
		$this->success       = $success;
		$this->operation     = $operation;
		$this->files_synced  = $files_synced;
		$this->files_failed  = $files_failed;
		$this->error         = $error;
		$this->synced_paths  = $synced_paths;
		$this->failed_paths  = $failed_paths;
	}

	/**
	 * Create a successful upload result.
	 *
	 * @since 2.1.0
	 *
	 * @param int      $attachment_id The attachment ID.
	 * @param string   $title         The attachment title.
	 * @param int      $files_synced  Number of files uploaded.
	 * @param string[] $synced_paths  Uploaded file paths.
	 * @return self
	 */
	public static function uploaded( int $attachment_id, string $title, int $files_synced, array $synced_paths = array() ): self {
		return new self(
			$attachment_id,
			$title,
			true,
			self::OPERATION_UPLOAD,
			$files_synced,
			0,
			null,
			$synced_paths,
			array()
		);
	}

	/**
	 * Create a successful delete result.
	 *
	 * @since 2.1.0
	 *
	 * @param int      $attachment_id The attachment ID.
	 * @param string   $title         The attachment title.
	 * @param int      $files_synced  Number of files deleted.
	 * @param string[] $synced_paths  Deleted file paths.
	 * @return self
	 */
	public static function deleted( int $attachment_id, string $title, int $files_synced, array $synced_paths = array() ): self {
		return new self(
			$attachment_id,
			$title,
			true,
			self::OPERATION_DELETE,
			$files_synced,
			0,
			null,
			$synced_paths,
			array()
		);
	}

	/**
	 * Create a failed result.
	 *
	 * @since 2.1.0
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $title         The attachment title.
	 * @param string $operation     The operation type.
	 * @param string $error         The error message.
	 * @return self
	 */
	public static function failed( int $attachment_id, string $title, string $operation, string $error ): self {
		return new self(
			$attachment_id,
			$title,
			false,
			$operation,
			0,
			1,
			$error,
			array(),
			array()
		);
	}

	/**
	 * Create a partial success result.
	 *
	 * @since 2.1.0
	 *
	 * @param int                   $attachment_id The attachment ID.
	 * @param string                $title         The attachment title.
	 * @param string                $operation     The operation type.
	 * @param int                   $files_synced  Number of files synced.
	 * @param int                   $files_failed  Number of files that failed.
	 * @param string[]              $synced_paths  Synced file paths.
	 * @param array<string, string> $failed_paths  Failed paths with errors.
	 * @return self
	 */
	public static function partial(
		int $attachment_id,
		string $title,
		string $operation,
		int $files_synced,
		int $files_failed,
		array $synced_paths,
		array $failed_paths
	): self {
		return new self(
			$attachment_id,
			$title,
			false,
			$operation,
			$files_synced,
			$files_failed,
			'Some files failed to sync.',
			$synced_paths,
			$failed_paths
		);
	}

	/**
	 * Create a skipped result (attachment not found or no file).
	 *
	 * @since 2.1.0
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $reason        The reason for skipping.
	 * @return self
	 */
	public static function skipped( int $attachment_id, string $reason ): self {
		return new self(
			$attachment_id,
			'',
			false,
			self::OPERATION_UPLOAD,
			0,
			0,
			$reason,
			array(),
			array()
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
	 * Check if the operation was successful.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_success(): bool {
		return $this->success;
	}

	/**
	 * Get the operation type.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_operation(): string {
		return $this->operation;
	}

	/**
	 * Get the number of files synced.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_files_synced(): int {
		return $this->files_synced;
	}

	/**
	 * Get the number of files that failed.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_files_failed(): int {
		return $this->files_failed;
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

	/**
	 * Get the synced file paths.
	 *
	 * @since 2.1.0
	 *
	 * @return string[]
	 */
	public function get_synced_paths(): array {
		return $this->synced_paths;
	}

	/**
	 * Get the failed file paths with errors.
	 *
	 * @since 2.1.0
	 *
	 * @return array<string, string>
	 */
	public function get_failed_paths(): array {
		return $this->failed_paths;
	}

	/**
	 * Check if this was a partial success (some files synced, some failed).
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_partial(): bool {
		return $this->files_synced > 0 && $this->files_failed > 0;
	}

	/**
	 * Get total number of files processed.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_total_files(): int {
		return $this->files_synced + $this->files_failed;
	}

	/**
	 * Convert to array for table display.
	 *
	 * @since 2.1.0
	 *
	 * @return array{ID: int, Title: string, Status: string, Files: string, Error: string}
	 */
	public function to_table_row(): array {
		$status = $this->success ? 'Success' : ( $this->is_partial() ? 'Partial' : 'Failed' );

		return array(
			'ID'     => $this->attachment_id,
			'Title'  => $this->title,
			'Status' => $status,
			'Files'  => sprintf( '%d/%d', $this->files_synced, $this->get_total_files() ),
			'Error'  => $this->error ?? '',
		);
	}
}
