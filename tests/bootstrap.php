<?php
/**
 * PHPUnit bootstrap file
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests;

use Yoast\WPTestUtils\WPIntegration;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$plugin_slug_argv = $GLOBALS['argv'];
$plugin_slug_key  = (int) array_search( '--testsuite', $plugin_slug_argv, true );

// Integration testing.
if ( $plugin_slug_key && 'integration' === $plugin_slug_argv[ $plugin_slug_key + 1 ] ) {
	$plugin_slug_tests_dir = getenv( 'WP_TESTS_DIR' );

	if ( ! $plugin_slug_tests_dir ) {
		$plugin_slug_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
	}

	if ( ! file_exists( $plugin_slug_tests_dir . '/includes/functions.php' ) ) {
		echo 'Could not find ' . $plugin_slug_tests_dir . '/includes/functions.php, have you run bin/install-wp-tests.sh?' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit( 1 );
	}

	if ( getenv( 'WP_PLUGIN_DIR' ) !== false ) {
		define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
	} else {
		define( 'WP_PLUGIN_DIR', dirname( __DIR__, 2 ) );
	}

	$GLOBALS['wp_tests_options'] = array(
		'active_plugins' => [ 's3-media-sync/s3-media-sync.php' ],
	);

	require_once dirname( __DIR__ ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

	/*
	 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
	 * and the custom autoloader for the TestCase and the mock object classes.
	 */
	WPIntegration\bootstrap_it();

	if ( ! defined( 'WP_PLUGIN_DIR' ) || file_exists( WP_PLUGIN_DIR . '/s3-media-sync/s3-media-sync.php' ) === false ) {
		echo PHP_EOL, 'ERROR: Please check whether the WP_PLUGIN_DIR environment variable is set and set to the correct value. The integration test suite won\'t be able to run without it.', PHP_EOL;
		exit( 1 );
	}

	// Additional necessary requires.
	require_once __DIR__ . '/trait-tests-reflection.php';
} else {
	// Unit testing bootstrap goes here.
}
