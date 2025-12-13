<?php
/**
 * Integration tests for S3 client functionality
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration;

use Aws\S3\S3Client;
use S3_Media_Sync\Tests\TestCase;

/**
 * Test case for S3 client functionality.
 *
 * @group integration
 * @group s3-client
 * @covers \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 */
class S3ClientTest extends TestCase {

	/**
	 * Test that get_s3_client returns null before setup is called.
	 *
	 * @covers \S3_Media_Sync::get_s3_client
	 */
	public function test_get_s3_client_returns_null_before_setup(): void {
		// Create a new instance without calling setup
		$settings_handler = new \S3_Media_Sync_Settings();
		$settings_handler->update_settings( $this->default_settings );
		$s3_media_sync = new \S3_Media_Sync( $settings_handler );

		// Client should be null before setup
		$this->assertNull( $s3_media_sync->get_s3_client() );
	}

	/**
	 * Test that get_s3_client returns the S3 client after setup.
	 *
	 * @covers \S3_Media_Sync::get_s3_client
	 */
	public function test_get_s3_client_returns_client_after_setup(): void {
		// Create mock client
		$this->create_mock_s3_client();

		// Setup the plugin
		$this->s3_media_sync->setup();

		// Client should be set after setup
		$client = $this->s3_media_sync->get_s3_client();
		$this->assertNotNull( $client );
	}

	/**
	 * Test that S3_Media_Sync throws exception with invalid settings.
	 *
	 * Note: Currently the constructor throws an exception when settings are invalid.
	 * This test documents that behavior - see issue #47 for graceful handling.
	 *
	 * @covers \S3_Media_Sync::__construct
	 */
	public function test_constructor_throws_with_invalid_region(): void {
		$this->expectException( \S3_Media_Sync\Exceptions\Invalid_Region_Exception::class );

		// Create instance with empty settings - should throw
		$settings_handler = new \S3_Media_Sync_Settings();
		$settings_handler->update_settings( [
			'bucket' => '',
			'key'    => '',
			'secret' => '',
			'region' => '',
		] );

		// This should throw an exception
		new \S3_Media_Sync( $settings_handler );
	}

	/**
	 * Test that get_s3_bucket returns the configured bucket name.
	 *
	 * @covers \S3_Media_Sync::get_s3_bucket
	 */
	public function test_get_s3_bucket_returns_bucket_name(): void {
		$bucket = $this->s3_media_sync->get_s3_bucket();
		$this->assertSame( 'test-bucket', $bucket );
	}

	/**
	 * Test that get_s3_bucket_url returns the S3 URL.
	 *
	 * @covers \S3_Media_Sync::get_s3_bucket_url
	 */
	public function test_get_s3_bucket_url_returns_s3_url(): void {
		$url = $this->s3_media_sync->get_s3_bucket_url();
		$this->assertSame( 's3://test-bucket', $url );
	}

	/**
	 * Test that get_settings_handler returns the settings handler.
	 *
	 * @covers \S3_Media_Sync::get_settings_handler
	 */
	public function test_get_settings_handler_returns_handler(): void {
		$handler = $this->s3_media_sync->get_settings_handler();
		$this->assertInstanceOf( \S3_Media_Sync_Settings::class, $handler );
	}

	/**
	 * Test that get_settings_handler can retrieve settings.
	 *
	 * @covers \S3_Media_Sync::get_settings_handler
	 */
	public function test_get_settings_handler_can_retrieve_settings(): void {
		$handler  = $this->s3_media_sync->get_settings_handler();
		$settings = $handler->get_settings();

		$this->assertSame( 'test-bucket', $settings['bucket'] );
		$this->assertSame( 'us-east-1', $settings['region'] );
	}
}
