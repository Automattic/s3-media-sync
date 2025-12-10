<?php
/**
 * PHPUnit bootstrap file
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests;

use Yoast\WPTestUtils\WPIntegration;

// Set up default test settings.
$GLOBALS['s3_media_sync_test_settings'] = [
	'bucket'     => 'test-bucket',
	'key'        => 'test-key',
	'secret'     => 'test-secret',
	'region'     => 'us-east-1',
	'object_acl' => 'public-read',
	'use_acl'    => true,
];

require_once dirname( __DIR__ ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$s3_media_sync_argv = $GLOBALS['argv'];
$s3_media_sync_key  = array_search( '--testsuite', $s3_media_sync_argv, true );

$is_integration = false !== $s3_media_sync_key && isset( $s3_media_sync_argv[ $s3_media_sync_key + 1 ] ) && 'integration' === $s3_media_sync_argv[ $s3_media_sync_key + 1 ];

// Integration testing.
if ( $is_integration ) {
	$_tests_dir = WPIntegration\get_path_to_wp_test_dir();

	if ( empty( $_tests_dir ) ) {
		echo 'ERROR: Could not find WordPress test library directory.' . PHP_EOL;
		echo 'Make sure wp-env is running: npm run wp-env start' . PHP_EOL;
		exit( 1 );
	}

	// Set up test settings in WordPress options table before loading WordPress.
	require_once $_tests_dir . '/includes/functions.php';
	\tests_add_filter(
		'pre_option_s3_media_sync_settings',
		function () {
			return $GLOBALS['s3_media_sync_test_settings'];
		}
	);

	// Load the plugin.
	\tests_add_filter(
		'muplugins_loaded',
		function (): void {
			require dirname( __DIR__ ) . '/s3-media-sync.php';
		}
	);

	/*
	 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
	 * and the custom autoloader for the TestCase and the mock object classes.
	 */
	WPIntegration\bootstrap_it();

	// Load test utilities.
	require_once __DIR__ . '/trait-tests-reflection.php';

} else {
	// Unit testing bootstrap.
	require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}
