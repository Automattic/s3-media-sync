<?php

namespace S3_Media_Sync\Tests\Unit\ValueObjects;

use PHPUnit\Framework\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\Region;
use S3_Media_Sync\Exceptions\Invalid_Bucket_Exception;

/**
 * Unit tests for S3_Bucket value object
 */
class S3BucketTest extends TestCase {

	/**
	 * Test creating bucket from settings
	 */
	public function test_from_settings_basic() {
		$settings = [
			'bucket' => 'my-test-bucket',
			'region' => 'us-west-2',
		];

		$bucket = S3_Bucket::from_settings($settings);

		$this->assertEquals('my-test-bucket', $bucket->get_name());
		$this->assertEquals('', $bucket->get_prefix());
		$this->assertEquals('us-west-2', $bucket->get_region()->get_identifier());
		$this->assertTrue($bucket->should_use_acl());
		$this->assertEquals('public-read', $bucket->get_object_acl());
	}

	/**
	 * Test creating bucket with prefix
	 */
	public function test_from_settings_with_prefix() {
		$settings = [
			'bucket' => 'my-test-bucket/uploads/media',
			'region' => 'eu-west-1',
		];

		$bucket = S3_Bucket::from_settings($settings);

		$this->assertEquals('my-test-bucket', $bucket->get_name());
		$this->assertEquals('uploads/media', $bucket->get_prefix());
		$this->assertEquals('eu-west-1', $bucket->get_region()->get_identifier());
	}

	/**
	 * Test creating bucket from string
	 */
	public function test_from_string() {
		$bucket = S3_Bucket::from_string('my-bucket/prefix');

		$this->assertEquals('my-bucket', $bucket->get_name());
		$this->assertEquals('prefix', $bucket->get_prefix());
	}

	/**
	 * Test bucket name validation
	 */
	public function test_invalid_bucket_name_throws_exception() {
		$this->expectException(Invalid_Bucket_Exception::class);
		$this->expectExceptionMessage('Bucket name cannot be empty');

		S3_Bucket::from_settings(['bucket' => '']);
	}

	/**
	 * Test bucket name with invalid characters
	 */
	public function test_invalid_bucket_name_characters() {
		$this->expectException(Invalid_Bucket_Exception::class);
		$this->expectExceptionMessage('Invalid bucket name');

		S3_Bucket::from_settings(['bucket' => 'My-Bucket-With-CAPS']);
	}

	/**
	 * Test bucket name too short
	 */
	public function test_bucket_name_too_short() {
		$this->expectException(Invalid_Bucket_Exception::class);
		$this->expectExceptionMessage('Invalid bucket name length');

		S3_Bucket::from_settings(['bucket' => 'ab']);
	}

	/**
	 * Test get full path with prefix
	 */
	public function test_get_full_path_with_prefix() {
		$bucket = S3_Bucket::from_string('my-bucket/uploads');

		$this->assertEquals('uploads/file.jpg', $bucket->get_full_path('file.jpg'));
		$this->assertEquals('uploads/file.jpg', $bucket->get_full_path('/file.jpg'));
	}

	/**
	 * Test get full path without prefix
	 */
	public function test_get_full_path_without_prefix() {
		$bucket = S3_Bucket::from_string('my-bucket');

		$this->assertEquals('file.jpg', $bucket->get_full_path('file.jpg'));
		$this->assertEquals('file.jpg', $bucket->get_full_path('/file.jpg'));
	}

	/**
	 * Test get AWS parameters
	 */
	public function test_get_aws_params() {
		$bucket = S3_Bucket::from_settings([
			'bucket' => 'my-bucket',
			'region' => 'us-east-1',
		]);

		$params = $bucket->get_aws_params();

		$this->assertArrayHasKey('Bucket', $params);
		$this->assertArrayHasKey('region', $params);
		$this->assertArrayHasKey('ACL', $params);
		$this->assertEquals('my-bucket', $params['Bucket']);
		$this->assertEquals('us-east-1', $params['region']);
		$this->assertEquals('public-read', $params['ACL']);
	}

	/**
	 * Test AWS parameters without ACL
	 */
	public function test_get_aws_params_without_acl() {
		$bucket = S3_Bucket::from_settings([
			'bucket' => 'my-bucket',
			'region' => 'us-east-1',
			'use_acl' => false,
		]);

		$params = $bucket->get_aws_params();

		$this->assertArrayNotHasKey('ACL', $params);
	}

	/**
	 * Test bucket equality
	 */
	public function test_bucket_equality() {
		$bucket1 = S3_Bucket::from_settings([
			'bucket' => 'my-bucket',
			'region' => 'us-east-1',
		]);

		$bucket2 = S3_Bucket::from_settings([
			'bucket' => 'my-bucket',
			'region' => 'us-east-1',
		]);

		$bucket3 = S3_Bucket::from_settings([
			'bucket' => 'other-bucket',
			'region' => 'us-east-1',
		]);

		$this->assertTrue($bucket1->equals($bucket2));
		$this->assertFalse($bucket1->equals($bucket3));
	}
}