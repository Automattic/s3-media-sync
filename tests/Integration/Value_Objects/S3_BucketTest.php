<?php
/**
 * Integration tests for S3_Bucket value object
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration\Value_Objects;

use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;

/**
 * Test case for S3_Bucket value object.
 *
 * @covers \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Exceptions\Invalid_Bucket_Exception
 * @uses \S3_Media_Sync\Exceptions\Invalid_Region_Exception
 * @group integration
 * @group value-objects
 */
class S3_BucketTest extends TestCase {

    protected array $test_settings = [
        'bucket' => 'test-bucket',
        'region' => 'us-east-1',
        'use_acl' => true,
        'object_acl' => 'public-read'
    ];

    /**
     * Test creating an S3_Bucket from settings
     */
    public function test_from_settings(): void {
        $bucket = S3_Bucket::from_settings($this->test_settings);

        Assert::assertInstanceOf(S3_Bucket::class, $bucket);
        Assert::assertSame('test-bucket', $bucket->get_name());
        Assert::assertInstanceOf(\S3_Media_Sync\Value_Objects\Region::class, $bucket->get_region());
        Assert::assertSame('us-east-1', $bucket->get_region()->get_identifier());
        Assert::assertTrue($bucket->should_use_acl());
        Assert::assertSame('public-read', $bucket->get_object_acl());
    }

    /**
     * Test bucket with prefix
     */
    public function test_bucket_with_prefix(): void {
        $settings = array_merge($this->test_settings, [
            'bucket' => 'test-bucket/prefix'
        ]);

        $bucket = S3_Bucket::from_settings($settings);

        Assert::assertSame('test-bucket', $bucket->get_name());
        Assert::assertSame('prefix', $bucket->get_prefix());
        Assert::assertSame('prefix/test-key', $bucket->get_full_path('test-key'));
    }

    /**
     * Test bucket without ACL
     */
    public function test_bucket_without_acl(): void {
        $settings = array_merge($this->test_settings, [
            'use_acl' => false,
            'object_acl' => null
        ]);

        $bucket = S3_Bucket::from_settings($settings);

        Assert::assertFalse($bucket->should_use_acl());
        Assert::assertNull($bucket->get_object_acl());
    }

    /**
     * Test invalid bucket name
     */
    public function test_invalid_bucket_name(): void {
        $settings = array_merge($this->test_settings, [
            'bucket' => ''
        ]);

        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Bucket_Exception::class);
        $this->expectExceptionMessage('Bucket name cannot be empty');
        S3_Bucket::from_settings($settings);
    }

    /**
     * Test invalid region
     */
    public function test_invalid_region(): void {
        $settings = array_merge($this->test_settings, [
            'region' => ''
        ]);

        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Region_Exception::class);
        S3_Bucket::from_settings($settings);
    }

    /**
     * Test invalid ACL
     */
    public function test_invalid_acl(): void {
        $settings = array_merge($this->test_settings, [
            'use_acl' => true,
            'object_acl' => 'invalid-acl'
        ]);

        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Bucket_Exception::class);
        $this->expectExceptionMessage('Invalid ACL value: invalid-acl');
        S3_Bucket::from_settings($settings);
    }

    /**
     * Test getting AWS parameters
     */
    public function test_get_aws_params(): void {
        $bucket = S3_Bucket::from_settings($this->test_settings);

        $expected_params = [
            'Bucket' => 'test-bucket',
            'region' => 'us-east-1',
            'ACL' => 'public-read'
        ];

        Assert::assertSame($expected_params, $bucket->get_aws_params());

        // Test without ACL
        $settings = array_merge($this->test_settings, [
            'use_acl' => false,
            'object_acl' => null
        ]);

        $bucket = S3_Bucket::from_settings($settings);
        $expected_params = [
            'Bucket' => 'test-bucket',
            'region' => 'us-east-1'
        ];

        Assert::assertSame($expected_params, $bucket->get_aws_params());
    }
} 
