<?php
/**
 * Integration tests for S3 Media Sync settings
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Aws\S3\S3Client;
use Mockery;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync;
use S3_Media_Sync_Settings;
use S3_Media_Sync_Client_Factory;

/**
 * Test case for S3 Media Sync settings functionality.
 *
 * @group integration
 * @group settings
 * @covers \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync_Client_Factory
 */
class SettingsTest extends TestCase {

	/**
	 * Set up before each test.
	 */
	public function set_up(): void {
		parent::set_up();
		
		// Clear any existing settings and errors
		delete_option( 's3_media_sync_settings' );
		global $wp_settings_errors;
		$wp_settings_errors = [];
	}

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
				true,
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
				false,
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
	 * @param bool   $should_validate Whether validation should be performed.
	 */
	public function test_invalid_settings_cause_admin_error_notice( array $settings, string $error_code, bool $should_validate ): void {
		// Clear any existing errors
		global $wp_settings_errors;
		$wp_settings_errors = [];

		// Update WordPress option and settings handler
		update_option( 's3_media_sync_settings', $settings );
		$this->settings_handler->update_settings( $settings );

		// Create a mock client if we should validate
		if ( $should_validate ) {
			$this->create_mock_s3_client([
				'error_code' => 'NoSuchBucket',
				'error_message' => 'The specified bucket does not exist',
				'should_succeed' => false
			]);
		}

		$this->s3_media_sync->setup();

		// Clear any errors that might have been set during setup
		$wp_settings_errors = [];

		$this->settings_handler->settings_validation( $settings );

		$admin_error_codes = wp_list_pluck( get_settings_errors(), 'code' );

		if ( $should_validate ) {
			Assert::assertContains( $error_code, $admin_error_codes, 'Settings validation should have failed' );
		} else {
			Assert::assertEmpty( $admin_error_codes, 'No validation errors should be present for empty settings' );
		}

		// Clean up
		delete_option( 's3_media_sync_settings' );
	}

	/**
	 * Test settings are properly saved.
	 *
	 * @dataProvider data_provider_invalid_settings
	 * 
	 * @param array  $settings    The settings to test with.
	 * @param string $error_code  The expected error code.
	 * @param bool   $should_validate Whether validation should be performed.
	 */
	public function test_settings_are_saved( array $settings, string $error_code, bool $should_validate ): void {
		// Update settings
		$this->settings_handler->update_settings( $settings );

		$this->s3_media_sync->setup();

		$saved_settings = $this->settings_handler->get_settings();

		Assert::assertSame( $settings, $saved_settings );
	}

	/**
	 * Clean up after each test.
	 */
	public function tear_down(): void {
		parent::tear_down();

		// Clean up settings and errors
		delete_option( 's3_media_sync_settings' );
		global $wp_settings_errors;
		$wp_settings_errors = [];

		Mockery::close();
	}
} 
