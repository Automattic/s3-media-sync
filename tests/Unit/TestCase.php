<?php
/**
 * Base test case for unit tests.
 *
 * @package S3_Media_Sync\Tests\Unit
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit;

use Brain\Monkey\Functions;
use Yoast\WPTestUtils\BrainMonkey\TestCase as BrainMonkeyTestCase;

/**
 * Base test case for unit tests.
 */
abstract class TestCase extends BrainMonkeyTestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		// Stub WordPress size_format function for value objects that use it.
		Functions\when( 'size_format' )->alias(
			function ( int $bytes, int $decimals = 2 ): string {
				$units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
				$i     = 0;

				while ( $bytes >= 1024 && $i < count( $units ) - 1 ) {
					$bytes /= 1024;
					$i++;
				}

				return number_format( $bytes, $decimals ) . ' ' . $units[ $i ];
			}
		);
	}
}
