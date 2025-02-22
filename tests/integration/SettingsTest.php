<?php
/**
 * Integration tests for S3 Media Sync settings
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync;
use S3_Media_Sync\Tests\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Test case for S3 Media Sync settings validation.
 *
 * @group integration
 * @group settings
 * @covers S3_Media_Sync
 */
class SettingsTest extends TestCase {

	/**
	 * Test data for settings validation scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_invalid_settings(): array {
		return [
			'invalid credentials' => [
				[
					'bucket'     => 'test-bucket',
					'key'        => 'test-key',
					'secret'     => 'test-secret',
					'region'     => 'test-region',
					'object_acl' => 'public-read',
				],
				's3-media-sync-settings-error',
			],
			'empty settings' => [
				[
					'bucket'     => '',
					'key'        => '',
					'secret'     => '',
					'region'     => '',
					'object_acl' => 'public-read',
				],
				's3-media-sync-settings-error',
			],
		];
	}

	/**
	 * Test settings validation error notices.
	 *
	 * @dataProvider data_provider_invalid_settings
	 * 
	 * @param array  $settings    The settings to test with.
	 * @param string $error_code  The expected error code.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_invalid_settings_cause_admin_error_notice( array $settings, string $error_code ): void {
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this->s3_media_sync->setup();
		$this->s3_media_sync->s3_media_sync_settings_validation( $settings );

		$admin_error_codes = wp_list_pluck( get_settings_errors(), 'code' );
		Assert::assertContains( $error_code, $admin_error_codes );
	}

	/**
	 * Test settings are properly saved.
	 *
	 * @dataProvider data_provider_invalid_settings
	 * 
	 * @param array $settings The settings to test with.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_settings_are_saved( array $settings ): void {
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this->s3_media_sync->setup();

		$saved_settings = $this::get_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings'
		);

		Assert::assertSame( $settings, $saved_settings );
	}
} 
