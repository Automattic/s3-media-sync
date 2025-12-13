<?php
/**
 * Integration tests for S3 Repository
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration\Services;

use Aws\S3\S3Client;
use Mockery;
use S3_Media_Sync\Services\S3_Repository;
use S3_Media_Sync\Services\S3_Repository_Interface;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\Value_Objects\S3_File;

/**
 * Test case for S3_Repository.
 *
 * @group integration
 * @group services
 * @covers \S3_Media_Sync\Services\S3_Repository
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync\Value_Objects\Region
 */
class S3RepositoryTest extends TestCase {

	/**
	 * The S3 repository instance.
	 *
	 * @var S3_Repository
	 */
	private S3_Repository $repository;

	/**
	 * Mock S3 client.
	 *
	 * @var S3Client|Mockery\MockInterface
	 */
	private $mock_client;

	/**
	 * Test bucket.
	 *
	 * @var S3_Bucket
	 */
	private S3_Bucket $bucket;

	/**
	 * Set up before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		// Create mock S3 client.
		$this->mock_client = Mockery::mock( S3Client::class );

		// Create test bucket.
		$this->bucket = S3_Bucket::from_settings( [
			'bucket'     => 'test-bucket',
			'region'     => 'us-east-1',
			'use_acl'    => true,
			'object_acl' => 'public-read',
		] );

		// Create repository with mock client.
		$this->repository = new S3_Repository( $this->mock_client, $this->bucket );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}

	/**
	 * Test that repository implements interface.
	 */
	public function test_implements_interface(): void {
		$this->assertInstanceOf( S3_Repository_Interface::class, $this->repository );
	}

	/**
	 * Test exists returns true when file exists.
	 */
	public function test_exists_returns_true_when_file_exists(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/2024/01/test.jpg' );

		$this->mock_client
			->shouldReceive( 'headObject' )
			->once()
			->with( [
				'Bucket' => 'test-bucket',
				'Key'    => 'wp-content/uploads/2024/01/test.jpg',
			] )
			->andReturn( [] );

		$this->assertTrue( $this->repository->exists( $file ) );
	}

	/**
	 * Test exists returns false when file does not exist.
	 */
	public function test_exists_returns_false_when_file_not_found(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/2024/01/missing.jpg' );

		$this->mock_client
			->shouldReceive( 'headObject' )
			->once()
			->andThrow( new \Aws\S3\Exception\S3Exception( 'Not Found', Mockery::mock( \Aws\CommandInterface::class ) ) );

		$this->assertFalse( $this->repository->exists( $file ) );
	}

	/**
	 * Test get_metadata returns metadata array.
	 */
	public function test_get_metadata_returns_metadata(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/2024/01/test.jpg' );

		$this->mock_client
			->shouldReceive( 'headObject' )
			->once()
			->andReturn( [
				'ContentLength' => 12345,
				'ContentType'   => 'image/jpeg',
				'ETag'          => '"abc123"',
				'LastModified'  => new \DateTime( '2024-01-15 10:00:00' ),
			] );

		$metadata = $this->repository->get_metadata( $file );

		$this->assertSame( 12345, $metadata['ContentLength'] );
		$this->assertSame( 'image/jpeg', $metadata['ContentType'] );
		$this->assertSame( 'abc123', $metadata['ETag'] );
		$this->assertInstanceOf( \DateTime::class, $metadata['LastModified'] );
	}

	/**
	 * Test get_metadata returns null when file not found.
	 */
	public function test_get_metadata_returns_null_when_not_found(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/missing.jpg' );

		$this->mock_client
			->shouldReceive( 'headObject' )
			->once()
			->andThrow( new \Aws\S3\Exception\S3Exception( 'Not Found', Mockery::mock( \Aws\CommandInterface::class ) ) );

		$this->assertNull( $this->repository->get_metadata( $file ) );
	}

	/**
	 * Test delete returns true on success.
	 */
	public function test_delete_returns_true_on_success(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/2024/01/test.jpg' );

		$this->mock_client
			->shouldReceive( 'deleteObject' )
			->once()
			->with( [
				'Bucket' => 'test-bucket',
				'Key'    => 'wp-content/uploads/2024/01/test.jpg',
			] )
			->andReturn( [] );

		$this->assertTrue( $this->repository->delete( $file ) );
	}

	/**
	 * Test delete returns false on failure.
	 */
	public function test_delete_returns_false_on_failure(): void {
		$file = S3_File::from_key( $this->bucket, 'wp-content/uploads/test.jpg' );

		$this->mock_client
			->shouldReceive( 'deleteObject' )
			->once()
			->andThrow( new \Aws\S3\Exception\S3Exception( 'Error', Mockery::mock( \Aws\CommandInterface::class ) ) );

		$this->assertFalse( $this->repository->delete( $file ) );
	}

	/**
	 * Test delete_batch with empty array.
	 */
	public function test_delete_batch_with_empty_array(): void {
		$result = $this->repository->delete_batch( [] );

		$this->assertEmpty( $result['deleted'] );
		$this->assertEmpty( $result['failed'] );
	}

	/**
	 * Test delete_batch returns deleted and failed arrays.
	 */
	public function test_delete_batch_returns_results(): void {
		$file1 = S3_File::from_key( $this->bucket, 'wp-content/uploads/file1.jpg' );
		$file2 = S3_File::from_key( $this->bucket, 'wp-content/uploads/file2.jpg' );

		$this->mock_client
			->shouldReceive( 'deleteObjects' )
			->once()
			->andReturn( [
				'Deleted' => [
					[ 'Key' => 'wp-content/uploads/file1.jpg' ],
				],
				'Errors' => [
					[
						'Key'     => 'wp-content/uploads/file2.jpg',
						'Message' => 'Access Denied',
					],
				],
			] );

		$result = $this->repository->delete_batch( [ $file1, $file2 ] );

		$this->assertCount( 1, $result['deleted'] );
		$this->assertCount( 1, $result['failed'] );
		$this->assertSame( 'Access Denied', $result['failed'][0]['error'] );
	}

	/**
	 * Test head_bucket returns true when accessible.
	 */
	public function test_head_bucket_returns_true_when_accessible(): void {
		$this->mock_client
			->shouldReceive( 'headBucket' )
			->once()
			->with( [ 'Bucket' => 'test-bucket' ] )
			->andReturn( [] );

		$this->assertTrue( $this->repository->head_bucket() );
	}

	/**
	 * Test head_bucket returns false when not accessible.
	 */
	public function test_head_bucket_returns_false_when_not_accessible(): void {
		$this->mock_client
			->shouldReceive( 'headBucket' )
			->once()
			->andThrow( new \Aws\S3\Exception\S3Exception( 'Not Found', Mockery::mock( \Aws\CommandInterface::class ) ) );

		$this->assertFalse( $this->repository->head_bucket() );
	}

	/**
	 * Test get_client returns the S3 client.
	 */
	public function test_get_client_returns_client(): void {
		$this->assertSame( $this->mock_client, $this->repository->get_client() );
	}

	/**
	 * Test get_bucket returns the bucket.
	 */
	public function test_get_bucket_returns_bucket(): void {
		$this->assertSame( $this->bucket, $this->repository->get_bucket() );
	}

	/**
	 * Test bucket with prefix.
	 */
	public function test_operations_with_bucket_prefix(): void {
		$bucket_with_prefix = S3_Bucket::from_settings( [
			'bucket'     => 'test-bucket/my-prefix',
			'region'     => 'us-east-1',
			'use_acl'    => false,
			'object_acl' => null,
		] );

		$repository = new S3_Repository( $this->mock_client, $bucket_with_prefix );
		$file = S3_File::from_key( $bucket_with_prefix, 'wp-content/uploads/test.jpg' );

		$this->mock_client
			->shouldReceive( 'headObject' )
			->once()
			->with( [
				'Bucket' => 'test-bucket',
				'Key'    => 'my-prefix/wp-content/uploads/test.jpg',
			] )
			->andReturn( [] );

		$this->assertTrue( $repository->exists( $file ) );
	}
}
