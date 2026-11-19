<?php
/**
 * Integration tests for running without a usable bucket.
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync\Tests\TestCase;

/**
 * The plugin has to load, and its settings page has to work, before a bucket
 * is configured: that's how an administrator gets to configure one.
 *
 * @group integration
 * @covers \S3_Media_Sync
 * @covers \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync_Tester
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Exceptions\Invalid_Bucket_Exception
 */
class MissingBucketTest extends TestCase {

	/**
	 * Replace the stored settings for the rest of the test.
	 *
	 * The test bootstrap supplies complete settings through a pre_option
	 * filter, so a later filter has to override it.
	 *
	 * @param array $settings Settings to return from get_option().
	 */
	private function use_settings( array $settings ): void {
		add_filter(
			'pre_option_s3_media_sync_settings',
			static function () use ( $settings ) {
				return $settings;
			},
			20
		);
	}

	/**
	 * Settings with everything but a usable bucket name.
	 *
	 * @return array[]
	 */
	public function data_unusable_buckets(): array {
		return [
			'no settings'          => [ [] ],
			'empty bucket'         => [
				[
					'bucket' => '',
					'key'    => 'test-key',
					'secret' => 'test-secret',
					'region' => 'us-east-1',
				],
			],
			'invalid bucket name' => [
				[
					'bucket' => 'Not_A_Valid_Bucket',
					'key'    => 'test-key',
					'secret' => 'test-secret',
					'region' => 'us-east-1',
				],
			],
		];
	}

	/**
	 * The plugins_loaded callback in the main plugin file runs without a bucket.
	 *
	 * @dataProvider data_unusable_buckets
	 *
	 * @param array $settings Stored settings.
	 */
	public function test_plugin_bootstrap_runs_without_a_usable_bucket( array $settings ): void {
		$this->use_settings( $settings );

		$ran = false;
		foreach ( $GLOBALS['wp_filter']['plugins_loaded']->callbacks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( $callback['function'] instanceof \Closure
					&& S3_MEDIA_SYNC_FILE === ( new \ReflectionFunction( $callback['function'] ) )->getFileName()
				) {
					$callback['function']();
					$ran = true;
				}
			}
		}

		$this->assertTrue( $ran, 'The plugin should register a plugins_loaded callback.' );
	}

	/**
	 * Without a usable bucket, setup leaves media handling alone.
	 *
	 * @dataProvider data_unusable_buckets
	 *
	 * @param array $settings Stored settings.
	 */
	public function test_setup_registers_no_media_hooks_without_a_usable_bucket( array $settings ): void {
		$this->use_settings( $settings );

		$s3_media_sync = new \S3_Media_Sync( new \S3_Media_Sync_Settings() );
		$s3_media_sync->setup();

		$this->assertFalse( has_filter( 'wp_handle_upload', [ $s3_media_sync, 'add_attachment_to_s3' ] ) );
		$this->assertNull( $s3_media_sync->get_s3_client() );
	}

	/**
	 * The Test S3 Access button reports a missing bucket instead of failing.
	 *
	 * @dataProvider data_unusable_buckets
	 *
	 * @param array $settings Stored settings.
	 */
	public function test_testing_access_without_a_usable_bucket_reports_an_error( array $settings ): void {
		$this->use_settings( $settings );
		$GLOBALS['wp_settings_errors'] = [];

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 's3_media_sync_test_access' );

		// handle_test_access() exits after redirecting, so stop it at the redirect.
		add_filter(
			'wp_redirect',
			static function () {
				throw new \RuntimeException( 'redirected' );
			}
		);

		try {
			( new \S3_Media_Sync_Settings() )->handle_test_access();
		} catch ( \RuntimeException $e ) {
			$this->assertSame( 'redirected', $e->getMessage() );
		}

		$codes = wp_list_pluck( get_settings_errors( 's3_media_sync_settings' ), 'code' );
		$this->assertContains( 's3-media-sync-test-error', $codes );
	}
}
