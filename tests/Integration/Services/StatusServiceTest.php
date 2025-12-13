<?php
/**
 * Integration tests for Status Service
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration\Services;

use Mockery;
use S3_Media_Sync\Services\S3_Repository_Interface;
use S3_Media_Sync\Services\Status_Service;
use S3_Media_Sync\Services\Status_Service_Interface;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Aws_Identity;
use S3_Media_Sync\Value_Objects\Connection_Status;
use S3_Media_Sync\Value_Objects\Region;
use S3_Media_Sync\Value_Objects\S3_Bucket;

/**
 * Test case for Status_Service.
 *
 * @group integration
 * @group services
 * @covers \S3_Media_Sync\Services\Status_Service
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\Connection_Status
 * @uses \S3_Media_Sync\Value_Objects\Aws_Identity
 */
class StatusServiceTest extends TestCase {

	/**
	 * Mock S3 repository.
	 *
	 * @var S3_Repository_Interface|Mockery\MockInterface
	 */
	private $mock_repository;

	/**
	 * Test bucket.
	 *
	 * @var S3_Bucket
	 */
	private S3_Bucket $bucket;

	/**
	 * Test settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Set up before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		// Create test bucket.
		$this->bucket = S3_Bucket::from_settings( array(
			'bucket'     => 'test-bucket',
			'region'     => 'us-east-1',
			'use_acl'    => true,
			'object_acl' => 'public-read',
		) );

		// Test settings.
		$this->settings = array(
			'bucket'          => 'test-bucket',
			'region'          => 'us-east-1',
			'key'             => 'AKIAIOSFODNN7EXAMPLE',
			'secret'          => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
			'use_acl'         => true,
			'object_acl'      => 'public-read',
			'sync_thumbnails' => true,
		);

		// Create mock repository.
		$this->mock_repository = Mockery::mock( S3_Repository_Interface::class );
		$this->mock_repository->shouldReceive( 'get_bucket' )->andReturn( $this->bucket );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();
		Mockery::close();
	}

	/**
	 * Test that service implements interface.
	 */
	public function test_implements_interface(): void {
		$service = new Status_Service( $this->mock_repository, $this->settings );

		$this->assertInstanceOf( Status_Service_Interface::class, $service );
	}

	/**
	 * Test get_connection_status returns connected when head_bucket succeeds.
	 */
	public function test_get_connection_status_returns_connected(): void {
		$this->mock_repository
			->shouldReceive( 'head_bucket' )
			->once()
			->andReturn( true );

		$service = new Status_Service( $this->mock_repository, $this->settings );
		$status  = $service->get_connection_status();

		$this->assertInstanceOf( Connection_Status::class, $status );
		$this->assertTrue( $status->is_connected() );
		$this->assertSame( 'test-bucket', $status->get_bucket() );
		$this->assertSame( 'us-east-1', $status->get_region() );
		$this->assertFalse( $status->has_error() );
	}

	/**
	 * Test get_connection_status returns failed when head_bucket fails.
	 */
	public function test_get_connection_status_returns_failed_on_error(): void {
		$this->mock_repository
			->shouldReceive( 'head_bucket' )
			->once()
			->andThrow( new \Exception( 'Access Denied' ) );

		$service = new Status_Service( $this->mock_repository, $this->settings );
		$status  = $service->get_connection_status();

		$this->assertFalse( $status->is_connected() );
		$this->assertTrue( $status->has_error() );
		$this->assertSame( 'Access Denied', $status->get_error() );
	}

	/**
	 * Test get_connection_status returns not_configured when missing settings.
	 */
	public function test_get_connection_status_returns_not_configured(): void {
		$incomplete_settings = array(
			'bucket' => 'test-bucket',
			// Missing region, key, secret.
		);

		$service = new Status_Service( $this->mock_repository, $incomplete_settings );
		$status  = $service->get_connection_status();

		$this->assertFalse( $status->is_connected() );
		$this->assertTrue( $status->has_error() );
		$this->assertStringContainsString( 'not properly configured', $status->get_error() );
	}

	/**
	 * Test has_required_settings returns true with complete settings.
	 */
	public function test_has_required_settings_returns_true(): void {
		$service = new Status_Service( $this->mock_repository, $this->settings );

		$this->assertTrue( $service->has_required_settings() );
	}

	/**
	 * Test has_required_settings returns false without bucket.
	 */
	public function test_has_required_settings_returns_false_without_bucket(): void {
		$settings = $this->settings;
		unset( $settings['bucket'] );

		$service = new Status_Service( $this->mock_repository, $settings );

		$this->assertFalse( $service->has_required_settings() );
	}

	/**
	 * Test has_required_settings returns false without region.
	 */
	public function test_has_required_settings_returns_false_without_region(): void {
		$settings = $this->settings;
		unset( $settings['region'] );

		$service = new Status_Service( $this->mock_repository, $settings );

		$this->assertFalse( $service->has_required_settings() );
	}

	/**
	 * Test has_required_settings returns false without credentials.
	 */
	public function test_has_required_settings_returns_false_without_credentials(): void {
		$settings = $this->settings;
		unset( $settings['key'], $settings['secret'] );

		$service = new Status_Service( $this->mock_repository, $settings );

		$this->assertFalse( $service->has_required_settings() );
	}

	/**
	 * Test get_settings_summary returns expected format.
	 */
	public function test_get_settings_summary_returns_expected_format(): void {
		$service = new Status_Service( $this->mock_repository, $this->settings );
		$summary = $service->get_settings_summary();

		$this->assertIsArray( $summary );
		$this->assertCount( 5, $summary );

		// Check structure.
		foreach ( $summary as $row ) {
			$this->assertArrayHasKey( 'Setting', $row );
			$this->assertArrayHasKey( 'Value', $row );
		}

		// Check specific values.
		$settings_map = array_column( $summary, 'Value', 'Setting' );
		$this->assertSame( 'test-bucket', $settings_map['Bucket'] );
		$this->assertSame( 'us-east-1', $settings_map['Region'] );
		$this->assertSame( 'Yes', $settings_map['Use ACLs'] );
		$this->assertSame( 'public-read', $settings_map['Object ACL'] );
		$this->assertSame( 'Yes', $settings_map['Sync Thumbnails'] );
	}

	/**
	 * Test get_settings_summary includes bucket prefix.
	 */
	public function test_get_settings_summary_includes_bucket_prefix(): void {
		$bucket_with_prefix = S3_Bucket::from_settings( array(
			'bucket'     => 'test-bucket/my-prefix',
			'region'     => 'us-east-1',
			'use_acl'    => false,
			'object_acl' => 'private',
		) );

		$this->mock_repository = Mockery::mock( S3_Repository_Interface::class );
		$this->mock_repository->shouldReceive( 'get_bucket' )->andReturn( $bucket_with_prefix );

		$service = new Status_Service( $this->mock_repository, $this->settings );
		$summary = $service->get_settings_summary();

		$settings_map = array_column( $summary, 'Value', 'Setting' );
		$this->assertSame( 'test-bucket/my-prefix', $settings_map['Bucket'] );
	}

	/**
	 * Test get_settings_summary handles missing optional settings.
	 */
	public function test_get_settings_summary_handles_missing_settings(): void {
		$minimal_settings = array(
			'bucket' => 'test-bucket',
			'region' => 'us-east-1',
			'key'    => 'test-key',
			'secret' => 'test-secret',
		);

		$service = new Status_Service( $this->mock_repository, $minimal_settings );
		$summary = $service->get_settings_summary();

		$settings_map = array_column( $summary, 'Value', 'Setting' );
		$this->assertSame( 'No', $settings_map['Use ACLs'] );
		$this->assertSame( 'Not set', $settings_map['Object ACL'] );
	}

	/**
	 * Test get_aws_identity returns null when missing settings.
	 */
	public function test_get_aws_identity_returns_null_without_settings(): void {
		$incomplete_settings = array(
			'bucket' => 'test-bucket',
		);

		$service  = new Status_Service( $this->mock_repository, $incomplete_settings );
		$identity = $service->get_aws_identity();

		$this->assertNull( $identity );
	}
}
