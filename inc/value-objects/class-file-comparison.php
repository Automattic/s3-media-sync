<?php

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing a comparison between a local file and an S3 file.
 */
class File_Comparison {
    /**
     * @var Local_File The local file
     */
    private Local_File $local_file;

    /**
     * @var S3_File|null The S3 file (if it exists)
     */
    private ?S3_File $s3_file;

    /**
     * @var bool Whether the files match
     */
    private bool $matches;

    /**
     * @var array List of differences between the files
     */
    private array $differences;

    /**
     * Create a new file comparison instance
     */
    private function __construct(
        Local_File $local_file,
        ?S3_File $s3_file,
        bool $matches,
        array $differences = []
    ) {
        $this->local_file = $local_file;
        $this->s3_file = $s3_file;
        $this->matches = $matches;
        $this->differences = $differences;
    }

    /**
     * Create a comparison between a local file and an S3 file
     */
    public static function compare(Local_File $local_file, ?S3_File $s3_file): self {
        if ($s3_file === null) {
            return new self(
                local_file: $local_file,
                s3_file: null,
                matches: false,
                differences: ['S3 file does not exist']
            );
        }

        $differences = [];

        // Compare file sizes if available
        $local_size = $local_file->get_size();
        $s3_size = $s3_file->get_size();
        if ($local_size !== null && $s3_size !== null && $local_size !== $s3_size) {
            $differences[] = sprintf(
                'Size mismatch: local=%d bytes, s3=%d bytes',
                $local_size,
                $s3_size
            );
        }

        // Compare content types if available
        $local_type = $local_file->get_mime_type();
        $s3_type = $s3_file->get_content_type();
        if ($local_type !== null && $s3_type !== null && $local_type !== $s3_type) {
            $differences[] = sprintf(
                'Content type mismatch: local=%s, s3=%s',
                $local_type,
                $s3_type
            );
        }

        // Compare MD5 hash with ETag if available
        // Note: This only works for files uploaded in a single part
        $local_hash = $local_file->get_md5_hash();
        $s3_etag = $s3_file->get_etag();
        if ($local_hash !== null && $s3_etag !== null) {
            $s3_hash = trim($s3_etag, '"'); // ETag includes quotes
            if ($local_hash !== $s3_hash) {
                $differences[] = 'Content hash mismatch';
            }
        }

        return new self(
            local_file: $local_file,
            s3_file: $s3_file,
            matches: empty($differences),
            differences: $differences
        );
    }

    /**
     * Get the local file
     */
    public function get_local_file(): Local_File {
        return $this->local_file;
    }

    /**
     * Get the S3 file
     */
    public function get_s3_file(): ?S3_File {
        return $this->s3_file;
    }

    /**
     * Check if the files match
     */
    public function matches(): bool {
        return $this->matches;
    }

    /**
     * Get the list of differences between the files
     */
    public function get_differences(): array {
        return $this->differences;
    }

    /**
     * Check if the S3 file exists
     */
    public function exists_in_s3(): bool {
        return $this->s3_file !== null;
    }

    /**
     * Get a human-readable summary of the comparison
     */
    public function get_summary(): string {
        if (!$this->exists_in_s3()) {
            return 'File does not exist in S3';
        }

        if ($this->matches()) {
            return 'Files match';
        }

        return implode(', ', $this->differences);
    }
} 
