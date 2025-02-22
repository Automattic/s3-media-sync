<?php
/**
 * Integration tests for S3 Media Sync hooks
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync;
use S3_Media_Sync\Tests\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Test case for S3 Media Sync hooks registration and functionality.
 *
 * @group integration
 * @group hooks
 * @covers S3_Media_Sync
 */
class HooksTest extends TestCase {

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
				true,
			],
			'incomplete settings' => [
				[
					'bucket'     => '',
					'key'        => '',
					'secret'     => '',
					'region'     => '',
					'object_acl' => 'public-read',
				],
				false,
			],
		];
	}

	/**
	 * Helper method for checking media sync hooks are registered.
	 *
	 * @param bool $should_be_registered Whether the hooks should be registered.
	 */
	private function assert_media_syncs_hooks_registered( bool $should_be_registered ): void {
		$hooks = [
			'wp_handle_upload'           => 'add_attachment_to_s3',
			'delete_attachment'          => 'delete_attachment_from_s3',
			'wp_save_image_editor_file' => 'add_updated_attachment_to_s3',
		];

		foreach ( $hooks as $hook => $callback ) {
			$priority = has_filter( $hook, [ $this->s3_media_sync, $callback ] );

			if ( $should_be_registered ) {
				Assert::assertIsInt( $priority, "Hook '$hook' should be registered" );
			} else {
				Assert::assertFalse( $priority, "Hook '$hook' should not be registered" );
			}
		}
	}

	/**
	 * Test hook registration based on settings.
	 *
	 * @dataProvider data_provider_settings
	 * 
	 * @param array $settings            The settings to test with.
	 * @param bool  $should_be_registered Whether the hooks should be registered.
	 * @throws \ReflectionException If reflection fails.
	 */
	public function test_media_syncs_hooks_registration( array $settings, bool $should_be_registered ): void {
		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'settings',
			$settings
		);

		$this->s3_media_sync->setup();

		$this->assert_media_syncs_hooks_registered( $should_be_registered );
	}
} 
