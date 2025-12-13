<?php
/**
 * Behat Feature Context for S3 Media Sync CLI tests.
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Behat;

use Automattic\BehatWpEnv\WpEnvFeatureContext;

/**
 * Feature context for S3 Media Sync Behat tests.
 */
final class FeatureContext extends WpEnvFeatureContext {

	/**
	 * Get the plugin slug.
	 *
	 * @return string The plugin slug.
	 */
	protected function get_plugin_slug(): string {
		return 's3-media-sync';
	}

	/**
	 * Plugin-specific cleanup after each scenario.
	 *
	 * @return void
	 */
	protected function plugin_specific_cleanup(): void {
		// Clean up any test attachments.
		$this->run_wp_cli_command( 'post delete $(wp post list --post_type=attachment --format=ids) --force 2>/dev/null || true' );

		// Clean up any plugin options.
		$this->run_wp_cli_command( 'option delete s3_media_sync_settings 2>/dev/null || true' );
	}

	/**
	 * Create a test attachment with a file.
	 *
	 * @Given /^an attachment "([^"]*)" exists$/
	 *
	 * @param string $filename The filename for the attachment.
	 * @return void
	 */
	public function an_attachment_exists( string $filename ): void {
		// Create a simple test file and import it.
		$title  = pathinfo( $filename, PATHINFO_FILENAME );
		$result = $this->run_wp_cli_command(
			sprintf(
				'eval "echo wp_insert_attachment( array( \"post_title\" => \"%s\", \"post_type\" => \"attachment\", \"post_mime_type\" => \"image/jpeg\" ) );"',
				$title
			)
		);

		$attachment_id = trim( $result );
		if ( ! empty( $attachment_id ) && is_numeric( $attachment_id ) ) {
			$this->variables['ATTACHMENT_ID'] = $attachment_id;
		}
	}

	/**
	 * Configure the plugin with test S3 settings.
	 *
	 * @Given /^S3 Media Sync is configured$/
	 *
	 * @return void
	 */
	public function s3_media_sync_is_configured(): void {
		// Set up test configuration (uses mock/test bucket).
		$settings = array(
			'bucket' => 'test-bucket',
			'region' => 'us-east-1',
			'key'    => 'test-key',
			'secret' => 'test-secret',
		);

		$this->run_wp_cli_command(
			sprintf(
				"option update s3_media_sync_settings '%s' --format=json",
				json_encode( $settings )
			)
		);
	}

	/**
	 * Create multiple test attachments.
	 *
	 * @Given /^(\d+) attachments exist$/
	 *
	 * @param int $count The number of attachments to create.
	 * @return void
	 */
	public function multiple_attachments_exist( int $count ): void {
		for ( $i = 1; $i <= $count; $i++ ) {
			$this->run_wp_cli_command(
				sprintf(
					'eval "wp_insert_attachment( array( \"post_title\" => \"Test Image %d\", \"post_type\" => \"attachment\", \"post_mime_type\" => \"image/jpeg\" ) );"',
					$i
				)
			);
		}
	}
}
