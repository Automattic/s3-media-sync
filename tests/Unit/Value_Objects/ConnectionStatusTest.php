<?php
/**
 * Unit tests for Connection_Status value object
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit\Value_Objects;

use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Connection_Status;

/**
 * Test case for Connection_Status value object.
 *
 * @group unit
 * @group value-objects
 * @covers \S3_Media_Sync\Value_Objects\Connection_Status
 */
class ConnectionStatusTest extends TestCase {

	/**
	 * Test connected factory method.
	 */
	public function test_connected_creates_successful_status(): void {
		$status = Connection_Status::connected( 'my-bucket', 'us-east-1' );

		$this->assertTrue( $status->is_connected() );
		$this->assertSame( 'my-bucket', $status->get_bucket() );
		$this->assertSame( 'us-east-1', $status->get_region() );
		$this->assertNull( $status->get_error() );
		$this->assertFalse( $status->has_error() );
	}

	/**
	 * Test failed factory method.
	 */
	public function test_failed_creates_error_status(): void {
		$status = Connection_Status::failed( 'my-bucket', 'us-west-2', 'Access Denied' );

		$this->assertFalse( $status->is_connected() );
		$this->assertSame( 'my-bucket', $status->get_bucket() );
		$this->assertSame( 'us-west-2', $status->get_region() );
		$this->assertSame( 'Access Denied', $status->get_error() );
		$this->assertTrue( $status->has_error() );
	}

	/**
	 * Test not_configured factory method.
	 */
	public function test_not_configured_creates_unconfigured_status(): void {
		$status = Connection_Status::not_configured();

		$this->assertFalse( $status->is_connected() );
		$this->assertSame( '', $status->get_bucket() );
		$this->assertSame( '', $status->get_region() );
		$this->assertTrue( $status->has_error() );
		$this->assertStringContainsString( 'not properly configured', $status->get_error() );
	}

	/**
	 * Test not_configured with custom message.
	 */
	public function test_not_configured_with_custom_message(): void {
		$status = Connection_Status::not_configured( 'Custom error message' );

		$this->assertSame( 'Custom error message', $status->get_error() );
	}

	/**
	 * Test is_connected returns correct value.
	 */
	public function test_is_connected_returns_correct_value(): void {
		$connected    = Connection_Status::connected( 'bucket', 'region' );
		$disconnected = Connection_Status::failed( 'bucket', 'region', 'error' );

		$this->assertTrue( $connected->is_connected() );
		$this->assertFalse( $disconnected->is_connected() );
	}

	/**
	 * Test has_error returns correct value.
	 */
	public function test_has_error_returns_correct_value(): void {
		$connected    = Connection_Status::connected( 'bucket', 'region' );
		$disconnected = Connection_Status::failed( 'bucket', 'region', 'error' );

		$this->assertFalse( $connected->has_error() );
		$this->assertTrue( $disconnected->has_error() );
	}
}
