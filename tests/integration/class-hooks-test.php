<?php
/**
 * Integration tests for S3 Media Sync hooks
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync;
use PHPUnit\Framework\TestCase;

/**
 * Test case for S3 Media Sync hooks registration and functionality.
 *
 * @group integration
 * @group hooks
 * @covers S3_Media_Sync
 */
class Hooks_Test extends WP_Integration_Test_Case {

	/**
	 * Test data for settings scenarios.
	 *
	 * @return array[] Array of test data.
	 */
	public function data_provider_settings(): array {
		return [
			'complete settings' => [
				[
					'bucket'     => 'test-bucket',
					'key'        => 'test-key',
					'secret'     => 'test-secret',
					'region'     => 'test-region',
					'object_acl' => 'public-read',
				],
				'assertIsInt',
			],
			'incomplete settings' => [
				[
					'bucket'     => '',
					'key'        => '',
					'secret'     => '',
					'region'     => '',
					'object_acl' => 'public-read',
				],
				'assertFalse',
			],
		];
	}

	/**
	 * Helper method for checking media sync hooks are registered.
	 *
	 * @param string $assertion The assertion to use.
	 */
	private function assert_media_syncs_hooks_registered( string $assertion ): void {
		$method = 'TestCase::' . $assertion;
		$method( has_filter( 'wp_handle_upload', [ $this->s3_media_sync, 'add_attachment_to_s3' ] ) );
		$method( has_action( 'delete_attachment', [ $this->s3_media_sync, 'delete_attachment_from_s3' ] ) );
		$method( has_filter( 'wp_save_image_editor_file', [ $this->s3_media_sync, 'add_updated_attachment_to_s3' ] ) );
	}

	/**
	 * Test hook registration based on settings.
	 *
	 * @dataProvider data_provider_settings
	 * 
	 * @param array  $settings  The settings to test with.
	 * @param string $assertion The assertion method to use.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_media_syncs_hooks_registration( array $settings, string $assertion ): void {
		parent::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this->s3_media_sync->setup();

		$this->assert_media_syncs_hooks_registered( $assertion );
	}
} 
