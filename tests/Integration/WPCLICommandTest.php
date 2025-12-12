<?php
/**
 * Integration tests for WP-CLI commands
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync\Tests\TestCase;

/**
 * Test case for WP-CLI command functionality.
 *
 * Note: These tests verify the command class structure and helper methods.
 * Full CLI testing would require the WP-CLI test framework.
 *
 * @group integration
 * @group wp-cli
 * @covers \S3_Media_Sync_WP_CLI_Command
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 */
class WPCLICommandTest extends TestCase {

	/**
	 * Set up the test.
	 *
	 * Load the WP-CLI command file if WP-CLI is available.
	 */
	public function set_up(): void {
		parent::set_up();

		// The WP-CLI command class is excluded from autoload and only loaded when WP-CLI is defined.
		// For testing, we need to manually load it if WP-CLI is available.
		if ( defined( 'WP_CLI' ) && ! class_exists( 'S3_Media_Sync_WP_CLI_Command' ) ) {
			require_once dirname( __DIR__, 2 ) . '/inc/class-s3-media-sync-wp-cli.php';
		}
	}

	/**
	 * Skip test if WP-CLI is not available.
	 */
	private function skip_if_no_wp_cli(): void {
		if ( ! defined( 'WP_CLI' ) || ! class_exists( 'S3_Media_Sync_WP_CLI_Command' ) ) {
			$this->markTestSkipped( 'WP-CLI is not available in this test environment.' );
		}
	}

	/**
	 * Test that the WP-CLI command class exists when WP-CLI is available.
	 */
	public function test_wp_cli_command_class_exists(): void {
		$this->skip_if_no_wp_cli();

		$this->assertTrue(
			class_exists( 'S3_Media_Sync_WP_CLI_Command' ),
			'S3_Media_Sync_WP_CLI_Command class should exist'
		);
	}

	/**
	 * Test that the WP-CLI command extends WP_CLI_Command.
	 */
	public function test_wp_cli_command_extends_correct_parent(): void {
		$this->skip_if_no_wp_cli();

		$reflection = new \ReflectionClass( 'S3_Media_Sync_WP_CLI_Command' );
		$parent     = $reflection->getParentClass();

		$this->assertNotFalse( $parent, 'S3_Media_Sync_WP_CLI_Command should have a parent class' );
		$this->assertSame( 'WP_CLI_Command', $parent->getName() );
	}

	/**
	 * Test that all expected public methods exist.
	 */
	public function test_wp_cli_command_has_expected_methods(): void {
		$this->skip_if_no_wp_cli();

		$expected_methods = [
			'upload',
			'upload_all',
			'rm',
			'status',
			'verify',
			'cleanup',
		];

		foreach ( $expected_methods as $method ) {
			$this->assertTrue(
				method_exists( 'S3_Media_Sync_WP_CLI_Command', $method ),
				"S3_Media_Sync_WP_CLI_Command should have the '{$method}' method"
			);
		}
	}

	/**
	 * Test that the status command method has correct visibility.
	 */
	public function test_status_method_is_public(): void {
		$this->skip_if_no_wp_cli();

		$reflection = new \ReflectionMethod( 'S3_Media_Sync_WP_CLI_Command', 'status' );
		$this->assertTrue( $reflection->isPublic(), 'status method should be public' );
	}

	/**
	 * Test that the verify command method has correct visibility.
	 */
	public function test_verify_method_is_public(): void {
		$this->skip_if_no_wp_cli();

		$reflection = new \ReflectionMethod( 'S3_Media_Sync_WP_CLI_Command', 'verify' );
		$this->assertTrue( $reflection->isPublic(), 'verify method should be public' );
	}

	/**
	 * Test that the cleanup command method has correct visibility.
	 */
	public function test_cleanup_method_is_public(): void {
		$this->skip_if_no_wp_cli();

		$reflection = new \ReflectionMethod( 'S3_Media_Sync_WP_CLI_Command', 'cleanup' );
		$this->assertTrue( $reflection->isPublic(), 'cleanup method should be public' );
	}

	/**
	 * Test that helper methods are private.
	 */
	public function test_helper_methods_are_private(): void {
		$this->skip_if_no_wp_cli();

		$private_methods = [
			'reset_local_object_cache',
			'reset_db_query_log',
			'get_s3_media_sync',
			'upload_file_to_s3',
		];

		foreach ( $private_methods as $method ) {
			$reflection = new \ReflectionMethod( 'S3_Media_Sync_WP_CLI_Command', $method );
			$this->assertTrue(
				$reflection->isPrivate(),
				"{$method} method should be private"
			);
		}
	}

	/**
	 * Test that get_s3_media_sync helper returns S3_Media_Sync instance.
	 */
	public function test_get_s3_media_sync_returns_correct_type(): void {
		$this->skip_if_no_wp_cli();

		// Create mock client first
		$this->create_mock_s3_client();

		// Use reflection to access private method
		$command    = new \S3_Media_Sync_WP_CLI_Command();
		$reflection = new \ReflectionMethod( $command, 'get_s3_media_sync' );
		$reflection->setAccessible( true );

		$result = $reflection->invoke( $command );

		$this->assertInstanceOf( \S3_Media_Sync::class, $result );
	}

	/**
	 * Test that S3_Media_Sync instance from helper has working methods.
	 */
	public function test_s3_media_sync_from_helper_has_working_methods(): void {
		$this->skip_if_no_wp_cli();

		// Create mock client first
		$this->create_mock_s3_client();

		// Use reflection to access private method
		$command    = new \S3_Media_Sync_WP_CLI_Command();
		$reflection = new \ReflectionMethod( $command, 'get_s3_media_sync' );
		$reflection->setAccessible( true );

		$s3_media_sync = $reflection->invoke( $command );

		// Test that we can get the bucket
		$this->assertSame( 'test-bucket', $s3_media_sync->get_s3_bucket() );

		// Test that we can get the bucket URL
		$this->assertSame( 's3://test-bucket', $s3_media_sync->get_s3_bucket_url() );

		// Test that we can get the settings handler
		$this->assertInstanceOf( \S3_Media_Sync_Settings::class, $s3_media_sync->get_settings_handler() );
	}

	/**
	 * Test that reset_db_query_log clears the query log.
	 */
	public function test_reset_db_query_log_clears_queries(): void {
		$this->skip_if_no_wp_cli();

		global $wpdb;

		// Add some fake queries to the log
		$wpdb->queries = [
			[ 'SELECT 1', 0.001, 'test' ],
			[ 'SELECT 2', 0.002, 'test' ],
		];

		// Use reflection to access private method
		$command    = new \S3_Media_Sync_WP_CLI_Command();
		$reflection = new \ReflectionMethod( $command, 'reset_db_query_log' );
		$reflection->setAccessible( true );

		$reflection->invoke( $command );

		$this->assertEmpty( $wpdb->queries, 'Query log should be empty after reset' );
	}
}
