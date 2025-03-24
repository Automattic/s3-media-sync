<?php

namespace S3_Media_Sync\Value_Objects;

use S3_Media_Sync\Exceptions\Invalid_File_Exception;

/**
 * Value object representing a file stored in S3.
 */
class S3_File {
    /**
     * @var S3_Bucket The bucket containing the file
     */
    private S3_Bucket $bucket;

    /**
     * @var string The key (path) of the file within the bucket
     */
    private string $key;

    /**
     * @var string|null The ETag of the file
     */
    private ?string $etag;

    /**
     * @var int|null The size of the file in bytes
     */
    private ?int $size;

    /**
     * @var string|null The content type of the file
     */
    private ?string $content_type;

    /**
     * Create a new S3 file instance
     */
    private function __construct(
        S3_Bucket $bucket,
        string $key,
        ?string $etag = null,
        ?int $size = null,
        ?string $content_type = null
    ) {
        if (empty($key)) {
            throw new Invalid_File_Exception('S3 key cannot be empty');
        }

        $this->bucket = $bucket;
        $this->key = $this->normalize_key($key);
        $this->etag = $etag;
        $this->size = $size;
        $this->content_type = $content_type;
    }

    /**
     * Create a file from basic information
     */
    public static function from_key(S3_Bucket $bucket, string $key): self {
        return new self($bucket, $key);
    }

    /**
     * Create a file from S3 object metadata
     */
    public static function from_metadata(S3_Bucket $bucket, string $key, array $metadata): self {
        $etag = $metadata['ETag'] ?? null;
        if ($etag) {
            $etag = trim($etag, '"'); // Remove quotes from ETag
        }

        return new self(
            $bucket,
            $key,
            $etag,
            $metadata['ContentLength'] ?? null,
            $metadata['ContentType'] ?? null
        );
    }

    /**
     * Get the bucket containing the file
     */
    public function get_bucket(): S3_Bucket {
        return $this->bucket;
    }

    /**
     * Get the key (path) of the file
     */
    public function get_key(): string {
        return $this->key;
    }

    /**
     * Get the full path of the file including bucket prefix
     */
    public function get_full_path(): string {
        return $this->bucket->get_full_path($this->key);
    }

    /**
     * Get the ETag of the file
     */
    public function get_etag(): ?string {
        return $this->etag;
    }

    /**
     * Get the size of the file in bytes
     */
    public function get_size(): ?int {
        return $this->size;
    }

    /**
     * Get the content type of the file
     */
    public function get_content_type(): ?string {
        return $this->content_type;
    }

    /**
     * Get the AWS SDK parameters for operations on this file
     */
    public function get_aws_params(): array {
        return [
            'Bucket' => $this->bucket->get_name(),
            'Key' => $this->key,
        ];
    }

    /**
     * Check if this file matches another S3 file
     */
    public function equals(S3_File $other): bool {
        return $this->bucket->equals($other->bucket) &&
               $this->key === $other->key &&
               $this->etag === $other->etag &&
               $this->size === $other->size &&
               $this->content_type === $other->content_type;
    }

    /**
     * Normalize a file key
     */
    private function normalize_key(string $key): string {
        // Convert Windows backslashes to forward slashes
        $key = str_replace('\\', '/', $key);

        // Remove any double slashes
        $key = preg_replace('#/+#', '/', $key);

        // Remove leading and trailing slashes
        return trim($key, '/');
    }
} 
