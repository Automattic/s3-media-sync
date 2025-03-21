<?php
/**
 * Integration tests for S3_File value object
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration\Value_Objects;

use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;

/**
 * Test case for S3_File value object.
 *
 * @covers \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Exceptions\Invalid_File_Exception
 * @group integration
 * @group value-objects
 */
class S3_FileTest extends TestCase {

    protected S3_Bucket $bucket;
    protected string $test_key = 'wp-content/uploads/test-file.txt';

    public function set_up(): void {
        parent::set_up();

        // Create a bucket object for testing
        $this->bucket = S3_Bucket::from_settings([
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
            'use_acl' => true,
            'object_acl' => 'public-read'
        ]);
    }

    /**
     * Test creating an S3_File from a key
     */
    public function test_from_key(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);

        Assert::assertInstanceOf(S3_File::class, $s3_file);
        Assert::assertSame($this->test_key, $s3_file->get_key());
        Assert::assertSame($this->bucket, $s3_file->get_bucket());
    }

    /**
     * Test getting file basename
     */
    public function test_get_basename(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);
        Assert::assertSame('test-file.txt', basename($s3_file->get_key()));

        // Test with a key containing no path
        $s3_file = S3_File::from_key($this->bucket, 'test.txt');
        Assert::assertSame('test.txt', basename($s3_file->get_key()));

        // Test with a key ending in slash
        $s3_file = S3_File::from_key($this->bucket, 'path/to');
        Assert::assertSame('to', basename($s3_file->get_key()));
    }

    /**
     * Test getting file directory
     */
    public function test_get_directory(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);
        Assert::assertSame('wp-content/uploads', dirname($s3_file->get_key()));

        // Test with no directory
        $s3_file = S3_File::from_key($this->bucket, 'test.txt');
        Assert::assertSame('.', dirname($s3_file->get_key()));

        // Test with multiple directory levels
        $s3_file = S3_File::from_key($this->bucket, 'path/to/nested/file.txt');
        Assert::assertSame('path/to/nested', dirname($s3_file->get_key()));
    }

    /**
     * Test getting file extension
     */
    public function test_get_extension(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);
        Assert::assertSame('txt', pathinfo($s3_file->get_key(), PATHINFO_EXTENSION));

        // Test with no extension
        $s3_file = S3_File::from_key($this->bucket, 'wp-content/uploads/test-file');
        Assert::assertSame('', pathinfo($s3_file->get_key(), PATHINFO_EXTENSION));

        // Test with multiple dots
        $s3_file = S3_File::from_key($this->bucket, 'wp-content/uploads/test.file.tar.gz');
        Assert::assertSame('gz', pathinfo($s3_file->get_key(), PATHINFO_EXTENSION));
    }

    /**
     * Test getting full path
     */
    public function test_get_full_path(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);
        Assert::assertSame($this->test_key, $s3_file->get_full_path());

        // Test with bucket prefix
        $bucket_with_prefix = S3_Bucket::from_settings([
            'bucket' => 'test-bucket/prefix',
            'region' => 'us-east-1',
            'use_acl' => true,
            'object_acl' => 'public-read'
        ]);
        $s3_file = S3_File::from_key($bucket_with_prefix, $this->test_key);
        Assert::assertSame('prefix/' . $this->test_key, $s3_file->get_full_path());
    }

    /**
     * Test getting AWS parameters
     */
    public function test_get_aws_params(): void {
        $s3_file = S3_File::from_key($this->bucket, $this->test_key);
        $expected_params = [
            'Bucket' => 'test-bucket',
            'Key' => $this->test_key
        ];
        Assert::assertSame($expected_params, $s3_file->get_aws_params());

        // Test with bucket prefix
        $bucket_with_prefix = S3_Bucket::from_settings([
            'bucket' => 'test-bucket/prefix',
            'region' => 'us-east-1',
            'use_acl' => true,
            'object_acl' => 'public-read'
        ]);
        $s3_file = S3_File::from_key($bucket_with_prefix, $this->test_key);
        $expected_params = [
            'Bucket' => 'test-bucket',
            'Key' => $this->test_key
        ];
        Assert::assertSame($expected_params, $s3_file->get_aws_params());
    }

    /**
     * Test invalid key
     */
    public function test_invalid_key(): void {
        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_File_Exception::class);
        $this->expectExceptionMessage('S3 key cannot be empty');
        S3_File::from_key($this->bucket, '');
    }
} 
