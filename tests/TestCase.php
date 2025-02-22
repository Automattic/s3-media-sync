<?php
/**
 * Base test case for all S3 Media Sync tests
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase as WPTestCase;

/**
 * Abstract base class for all test case implementations.
 */
abstract class TestCase extends WPTestCase {
	use Tests_Reflection;

	/**
	 * Holds a reference to the S3_Media_Sync instance used for testing.
	 *
	 * @var \S3_Media_Sync $s3_media_sync The S3_Media_Sync instance.
	 */
	protected \S3_Media_Sync $s3_media_sync;

	/**
	 * Prepares the test environment before each test.
	 *
	 * The plugin's default s3_media_sync_setup instantiation is removed
	 * so the setup method can be invoked manually within individual tests.
	 */
	public function set_up(): void {
		parent::set_up();

		// Ensure the AWS SDK can be loaded.
		if ( ! class_exists( '\\Aws\\S3\\S3Client' ) ) {
			// Require AWS Autoloader file.
			require_once dirname( __FILE__, 2 ) . '/vendor/autoload.php';
		}

		$this->s3_media_sync = \S3_Media_Sync::init();

		remove_action( 'plugins_loaded', [ $this->s3_media_sync, 's3_media_sync_setup' ] );
	}

	/**
	 * Nullify the S3_Media_Sync instance after each test.
	 *
	 * @throws \ReflectionException If reflection fails.
	 */
	public function tear_down(): void {
		parent::tear_down();

		$this::set_private_property(
			$this->s3_media_sync::class,
			$this->s3_media_sync,
			'instance',
			null
		);
	}
} 
