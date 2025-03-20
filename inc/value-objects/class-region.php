<?php

namespace S3_Media_Sync\Value_Objects;

use S3_Media_Sync\Exceptions\Invalid_Region_Exception;

/**
 * Value object representing an AWS region.
 */
class Region {
    /**
     * List of valid AWS regions
     * @see https://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
     */
    private const VALID_REGIONS = [
        'us-east-1',      // US East (N. Virginia)
        'us-east-2',      // US East (Ohio)
        'us-west-1',      // US West (N. California)
        'us-west-2',      // US West (Oregon)
        'af-south-1',     // Africa (Cape Town)
        'ap-east-1',      // Asia Pacific (Hong Kong)
        'ap-south-1',     // Asia Pacific (Mumbai)
        'ap-northeast-1', // Asia Pacific (Tokyo)
        'ap-northeast-2', // Asia Pacific (Seoul)
        'ap-northeast-3', // Asia Pacific (Osaka)
        'ap-southeast-1', // Asia Pacific (Singapore)
        'ap-southeast-2', // Asia Pacific (Sydney)
        'ap-southeast-3', // Asia Pacific (Jakarta)
        'ca-central-1',   // Canada (Central)
        'eu-central-1',   // Europe (Frankfurt)
        'eu-west-1',      // Europe (Ireland)
        'eu-west-2',      // Europe (London)
        'eu-west-3',      // Europe (Paris)
        'eu-north-1',     // Europe (Stockholm)
        'eu-south-1',     // Europe (Milan)
        'me-south-1',     // Middle East (Bahrain)
        'sa-east-1',      // South America (São Paulo)
    ];

    /**
     * @var string The region identifier
     */
    private string $identifier;

    /**
     * Create a new region instance
     */
    private function __construct(string $identifier) {
        $this->identifier = $this->normalize($identifier);
        $this->validate();
    }

    /**
     * Create a region from a string identifier
     */
    public static function from_string(string $identifier): self {
        return new self($identifier);
    }

    /**
     * Create a region using the default region (us-east-1)
     */
    public static function default(): self {
        return new self('us-east-1');
    }

    /**
     * Get the region identifier
     */
    public function get_identifier(): string {
        return $this->identifier;
    }

    /**
     * Check if this region matches another region
     */
    public function equals(Region $other): bool {
        return $this->identifier === $other->identifier;
    }

    /**
     * Get the display name for the region
     */
    public function get_display_name(): string {
        $names = [
            'us-east-1' => 'US East (N. Virginia)',
            'us-east-2' => 'US East (Ohio)',
            'us-west-1' => 'US West (N. California)',
            'us-west-2' => 'US West (Oregon)',
            'af-south-1' => 'Africa (Cape Town)',
            'ap-east-1' => 'Asia Pacific (Hong Kong)',
            'ap-south-1' => 'Asia Pacific (Mumbai)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'ap-northeast-2' => 'Asia Pacific (Seoul)',
            'ap-northeast-3' => 'Asia Pacific (Osaka)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-southeast-3' => 'Asia Pacific (Jakarta)',
            'ca-central-1' => 'Canada (Central)',
            'eu-central-1' => 'Europe (Frankfurt)',
            'eu-west-1' => 'Europe (Ireland)',
            'eu-west-2' => 'Europe (London)',
            'eu-west-3' => 'Europe (Paris)',
            'eu-north-1' => 'Europe (Stockholm)',
            'eu-south-1' => 'Europe (Milan)',
            'me-south-1' => 'Middle East (Bahrain)',
            'sa-east-1' => 'South America (São Paulo)',
        ];

        return $names[$this->identifier] ?? $this->identifier;
    }

    /**
     * Get a list of all valid regions
     * 
     * @return Region[]
     */
    public static function get_all(): array {
        return array_map(
            fn(string $identifier) => new self($identifier),
            self::VALID_REGIONS
        );
    }

    /**
     * Get a list of all valid region identifiers
     * 
     * @return string[]
     */
    public static function get_valid_regions(): array {
        return self::VALID_REGIONS;
    }

    /**
     * Convert to string
     */
    public function __toString(): string {
        return $this->identifier;
    }

    /**
     * Validate the region identifier
     *
     * @throws Invalid_Region_Exception
     */
    private function validate(): void {
        if (!in_array($this->identifier, self::VALID_REGIONS, true)) {
            throw new Invalid_Region_Exception(
                sprintf(
                    'Invalid region: %s. Must be one of: %s',
                    $this->identifier,
                    implode(', ', self::VALID_REGIONS)
                )
            );
        }
    }

    /**
     * Normalize a region identifier
     */
    private function normalize(string $identifier): string {
        return strtolower(trim($identifier));
    }
} 
