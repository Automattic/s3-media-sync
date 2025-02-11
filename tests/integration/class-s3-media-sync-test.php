<?php
/**
 * Integration tests for S3_Media_Sync
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync;

/**
 * S3_Media_Sync test case.
 *
 * @covers S3_Media_Sync
 */
class S3_Media_Sync_Test extends WP_Integration_Test_Case {

	private array $complete_settings
		= [
			'bucket'     => 'test-bucket',
			'key'        => 'test-key',
			'secret'     => 'test-secret',
			'region'     => 'test-region',
			'object_acl' => 'public-read',
		];

	private array $incomplete_settings
		= [
			'bucket'     => '',
			'key'        => '',
			'secret'     => '',
			'region'     => '',
			'object_acl' => 'public-read',
		];

	/**
	 * Helper method for that checking media sync hooks are registered.
	 *
	 * @param string $assertion The assertion to use.
	 */
	private function assert_media_syncs_hooks_registered( string $assertion ): void {
		self::$assertion( has_filter( 'wp_handle_upload', [ $this->s3_media_sync, 'add_attachment_to_s3' ] ) );
		self::$assertion( has_action( 'delete_attachment', [ $this->s3_media_sync, 'delete_attachment_from_s3' ] ) );
		self::$assertion( has_filter( 'wp_save_image_editor_file', [ $this->s3_media_sync, 'add_updated_attachment_to_s3' ] ) );
	}

	/**
	 * @testdox Allow media syncs when required settings set
	 *
	 * @throws \ReflectionException
	 */
	public function test_allow_media_syncs_when_required_settings_set(): void {
		parent::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->complete_settings
		);

		$this->s3_media_sync->setup();

		// Both has_filter() and has_action() return the priority of the hook if it exists.
		$this->assert_media_syncs_hooks_registered( 'assertIsInt' );
	}

	/**
	 * @testdox Disable media syncs when missing required settings
	 *
	 * @throws \ReflectionException
	 */
	public function test_disable_media_syncs_when_missing_required_settings(): void {
		parent::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->incomplete_settings
		);

		$this->s3_media_sync->setup();

		$this->assert_media_syncs_hooks_registered( 'assertFalse' );
	}

	/**
	 * @testdox Stream wrapper registered
	 *
	 * @throws \ReflectionException
	 */
	public function test_stream_wrapper_registered(): void {
		parent::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->complete_settings
		);

		$this->s3_media_sync->setup();

		$this->assertContains( 's3', stream_get_wrappers() );
	}

	/**
	 * @testdox Invalid s3 settings cause admin error notice
	 *
	 * @throws \ReflectionException
	 */
	public function test_invalid_s3_settings_cause_admin_error_notice(): void {
		parent::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$this->complete_settings
		);

		$this->s3_media_sync->setup();

		$this->s3_media_sync->s3_media_sync_settings_validation( $this->complete_settings );

		$admin_error_codes = wp_list_pluck( get_settings_errors(), 'code' );

		$this->assertContains( 's3-media-sync-settings-error', $admin_error_codes );
	}

	/**
	 * Nullify the S3_Media_Sync instance after each test.
	 *
	 * @throws \ReflectionException
	 */
	public function tear_down(): void {
		parent::tear_down();
	}
}
