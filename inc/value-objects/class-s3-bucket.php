<?php

namespace S3_Media_Sync\Value_Objects;

use S3_Media_Sync\Exceptions\Invalid_Bucket_Exception;

/**
 * Value object representing an S3 bucket configuration.
 */
class S3_Bucket {
    /**
     * Valid ACL values for S3 objects
     */
    private const VALID_ACLS = [
        'private',
        'public-read',
        'public-read-write',
        'authenticated-read',
        'aws-exec-read',
        'bucket-owner-read',
        'bucket-owner-full-control'
    ];

    /**
     * @var string The base bucket name without prefix
     */
    private string $name;

    /**
     * @var string Optional path prefix within the bucket
     */
    private string $prefix;

    /**
     * @var Region The AWS region for the bucket
     */
    private Region $region;

    /**
     * @var bool Whether to use ACLs when uploading objects
     */
    private bool $use_acl;

    /**
     * @var string|null The ACL to apply to uploaded objects
     */
    private ?string $object_acl;

    /**
     * Create a new bucket instance
     */
    private function __construct(
        string $name,
        string $prefix = '',
        Region|string $region = 'us-east-1',
        bool $use_acl = true,
        ?string $object_acl = 'public-read'
    ) {
        $this->validate_name($name);
        $this->validate_prefix($prefix);
        $this->validate_acl($use_acl, $object_acl);

        $this->name = $name;
        $this->prefix = $this->normalize_prefix($prefix);
        $this->region = is_string($region) ? Region::from_string($region) : $region;
        $this->use_acl = $use_acl;
        
        // Sanitize ACL value before assignment
        $this->object_acl = $object_acl !== null ? strip_tags($object_acl) : null;
    }

    /**
     * Create a bucket instance from WordPress settings
     */
    public static function from_settings(array $settings): self {
        $bucket_string = $settings['bucket'] ?? '';
        $region = isset($settings['region']) ? Region::from_string($settings['region']) : Region::default();
        $use_acl = isset($settings['use_acl']) ? (bool) $settings['use_acl'] : true;
        $object_acl = $settings['object_acl'] ?? 'public-read';

        // Split bucket string into name and prefix
        $parts = explode('/', $bucket_string, 2);
        $name = $parts[0];
        $prefix = $parts[1] ?? '';

        return new self(
            name: $name,
            prefix: $prefix,
            region: $region,
            use_acl: $use_acl,
            object_acl: $use_acl ? $object_acl : null
        );
    }

    /**
     * Create a bucket instance from a bucket string (name/prefix)
     */
    public static function from_string(string $bucket_string, array $settings = []): self {
        $parts = explode('/', $bucket_string, 2);
        $name = $parts[0];
        $prefix = $parts[1] ?? '';

        $region = isset($settings['region']) 
            ? Region::from_string($settings['region']) 
            : Region::default();

        return new self(
            name: $name,
            prefix: $prefix,
            region: $region,
            use_acl: $settings['use_acl'] ?? true,
            object_acl: $settings['object_acl'] ?? 'public-read'
        );
    }

    /**
     * Get the bucket name
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Get the bucket prefix
     */
    public function get_prefix(): string {
        return $this->prefix;
    }

    /**
     * Get the bucket region
     */
    public function get_region(): Region {
        return $this->region;
    }

    /**
     * Whether ACLs should be used
     */
    public function should_use_acl(): bool {
        return $this->use_acl;
    }

    /**
     * Get the object ACL setting
     */
    public function get_object_acl(): ?string {
        return $this->object_acl;
    }

    /**
     * Get the full path for a key, including prefix
     */
    public function get_full_path(string $key): string {
        $key = ltrim($key, '/');
        return empty($this->prefix) ? $key : trailingslashit($this->prefix) . $key;
    }

    /**
     * Append a path to the bucket prefix
     */
    public function append_path(string $path): string {
        $path = ltrim($path, '/');
        return empty($this->prefix) 
            ? $path 
            : trailingslashit($this->prefix) . $path;
    }

    /**
     * Get the S3 key from a path, removing the prefix if present
     */
    public function get_key_from_path(string $path): string {
        if (empty($this->prefix)) {
            return ltrim($path, '/');
        }

        $prefix = trailingslashit($this->prefix);
        if (strpos($path, $prefix) === 0) {
            return substr($path, strlen($prefix));
        }

        return ltrim($path, '/');
    }

    /**
     * Get the AWS SDK bucket parameters
     */
    public function get_aws_params(): array {
        $params = [
            'Bucket' => $this->name,
            'region' => $this->region->get_identifier(),
        ];

        if ($this->use_acl && $this->object_acl) {
            $params['ACL'] = $this->object_acl;
        }

        return $params;
    }

    /**
     * Validate the bucket name
     *
     * @throws Invalid_Bucket_Exception
     */
    private function validate_name( $name ): void {
        if (empty($name)) {
            throw new Invalid_Bucket_Exception('Bucket name cannot be empty');
        }

        // AWS bucket naming rules
        if (!preg_match('/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/', $name)) {
            throw new Invalid_Bucket_Exception(
                'Invalid bucket name. Must contain only lowercase letters, numbers, dots, and hyphens. ' .
                'Must begin and end with a letter or number.'
            );
        }

        if (strlen($name) < 3 || strlen($name) > 63) {
            throw new Invalid_Bucket_Exception(
                'Invalid bucket name length. Must be between 3 and 63 characters.'
            );
        }
    }

    /**
     * Validate the bucket prefix
     *
     * @throws Invalid_Bucket_Exception
     */
    private function validate_prefix( $prefix ): void {
        if (empty($prefix)) {
            return;
        }

        // Check for invalid characters
        if (preg_match('/[^a-zA-Z0-9\/_-]/', $prefix)) {
            throw new Invalid_Bucket_Exception(
                'Invalid prefix. Must contain only letters, numbers, underscores, forward slashes, and hyphens.'
            );
        }
    }

    /**
     * Validate the ACL setting
     *
     * @throws Invalid_Bucket_Exception
     */
    private function validate_acl( $use_acl, $object_acl ): void {
        if ( ! $use_acl || $object_acl === null) {
            return;
        }

        if ( ! in_array( $object_acl, self::VALID_ACLS, true ) ) {
            throw new Invalid_Bucket_Exception(
                sprintf(
                    'Invalid ACL value: %s. Must be one of: %s',
                    $object_acl,
                    implode(', ', self::VALID_ACLS)
                )
            );
        }
    }

    /**
     * Normalize a prefix string
     */
    private function normalize_prefix(string $prefix): string {
        $prefix = trim($prefix);
        $prefix = trim($prefix, '/');
        return $prefix;
    }

    /**
     * Get the ACL setting for this bucket.
     *
     * @return string The ACL setting.
     */
    public function get_acl(): string {
        return $this->object_acl ?? 'public-read';
    }
} 
