<?php

namespace S3_Media_Sync\Value_Objects;

use S3_Media_Sync\Exceptions\Invalid_File_Exception;

/**
 * Value object representing a file on the local filesystem.
 */
class Local_File {
    /**
     * @var string The absolute path to the file
     */
    private string $path;

    /**
     * @var string|null The file's MIME type
     */
    private ?string $mime_type;

    /**
     * @var int|null The file size in bytes
     */
    private ?int $size;

    /**
     * @var string|null MD5 hash of the file contents
     */
    private ?string $md5_hash;

    /**
     * Create a new local file instance
     */
    private function __construct(
        string $path,
        ?string $mime_type = null,
        ?int $size = null,
        ?string $md5_hash = null
    ) {
        $this->path = $this->normalize_path($path);
        $this->mime_type = $mime_type;
        $this->size = $size;
        $this->md5_hash = $md5_hash;

        $this->validate();
    }

    /**
     * Create a file from a path
     */
    public static function from_path(string $path): self {
        $path = realpath($path);
        if (!$path) {
            throw new Invalid_File_Exception('File does not exist: ' . $path);
        }

        $mime_type = mime_content_type($path);
        $size = filesize($path);
        $md5_hash = md5_file($path);

        return new self($path, $mime_type, $size, $md5_hash);
    }

    /**
     * Create a file with metadata
     */
    public static function from_metadata(
        string $path,
        string $mime_type,
        int $size,
        string $md5_hash
    ): self {
        return new self($path, $mime_type, $size, $md5_hash);
    }

    /**
     * Get the absolute path to the file
     */
    public function get_path(): string {
        return $this->path;
    }

    /**
     * Get the file's basename
     */
    public function get_basename(): string {
        return basename($this->path);
    }

    /**
     * Get the file's directory path
     */
    public function get_directory(): string {
        return dirname($this->path);
    }

    /**
     * Get the file's extension
     */
    public function get_extension(): string {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file's MIME type
     */
    public function get_mime_type(): ?string {
        if ($this->mime_type === null && file_exists($this->path)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mime_type = finfo_file($finfo, $this->path);
            finfo_close($finfo);
        }

        return $this->mime_type;
    }

    /**
     * Get the file size in bytes
     */
    public function get_size(): ?int {
        if ($this->size === null && file_exists($this->path)) {
            $this->size = filesize($this->path);
        }

        return $this->size;
    }

    /**
     * Get the MD5 hash of the file contents
     */
    public function get_md5_hash(): ?string {
        if ($this->md5_hash === null && file_exists($this->path)) {
            $this->md5_hash = md5_file($this->path);
        }

        return $this->md5_hash;
    }

    /**
     * Check if the file exists
     */
    public function exists(): bool {
        return file_exists($this->path);
    }

    /**
     * Check if this file matches another local file
     */
    public function equals(Local_File $other): bool {
        return $this->path === $other->path;
    }

    /**
     * Validate the file path
     *
     * @throws Invalid_File_Exception
     */
    private function validate(): void {
        if (empty($this->path)) {
            throw new Invalid_File_Exception('File path cannot be empty');
        }

        if ($this->mime_type !== null && !preg_match('/^[\w\-\+\.]+\/[\w\-\+\.]+$/', $this->mime_type)) {
            throw new Invalid_File_Exception('Invalid MIME type: ' . $this->mime_type);
        }

        if ($this->size !== null && $this->size < 0) {
            throw new Invalid_File_Exception('File size cannot be negative');
        }

        if ($this->md5_hash !== null && !preg_match('/^[a-f0-9]{32}$/', $this->md5_hash)) {
            throw new Invalid_File_Exception('Invalid MD5 hash: ' . $this->md5_hash);
        }
    }

    /**
     * Normalize a file path
     */
    private function normalize_path(string $path): string {
        // Convert Windows backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove any double slashes
        $path = preg_replace('#/+#', '/', $path);

        // Remove trailing slash
        return rtrim($path, '/');
    }
} 
