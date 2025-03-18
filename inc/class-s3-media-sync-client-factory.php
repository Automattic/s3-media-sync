<?php
/**
 * S3 Client Factory
 *
 * Creates and configures S3 client instances.
 *
 * @package S3_Media_Sync
 */

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;

/**
 * Class S3_Media_Sync_Client_Factory
 *
 * Factory class for creating configured S3 client instances.
 *
 * @since 1.0.0
 */
class S3_Media_Sync_Client_Factory {

	/**
	 * Creates a new S3 client instance with the provided settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Array of S3 settings (key, secret, region).
	 * @return S3ClientInterface Configured S3 client instance.
	 * @throws \InvalidArgumentException If required settings are missing.
	 */
	public function create( array $settings ): S3ClientInterface {
		if ( empty( $settings['region'] ) ) {
			throw new \InvalidArgumentException( 'Region is required to create an S3 client.' );
		}

		$params = [
			'version' => 'latest',
			'signature_version' => 'v4',
			'region' => $settings['region']
		];

		if ( isset( $settings['key'] ) && isset( $settings['secret'] ) && $settings['key'] && $settings['secret'] ) {
			$params['credentials'] = [
				'key'    => $settings['key'],
				'secret' => $settings['secret']
			];
		}

		// Configure proxy if WordPress proxy is defined
		if ( defined( 'WP_PROXY_HOST' ) && defined( 'WP_PROXY_PORT' ) ) {
			$proxy_auth = '';
			$proxy_address = WP_PROXY_HOST . ':' . WP_PROXY_PORT;

			if ( defined( 'WP_PROXY_USERNAME' ) && defined( 'WP_PROXY_PASSWORD' ) ) {
				$proxy_auth = WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD . '@';
			}

            $params['request.options']['proxy'] = $proxy_auth . $proxy_address;

		}

		return new S3Client( $params );
	}

	/**
	 * Configures the stream wrapper with the provided client and settings.
	 *
	 * @since 1.0.0
	 *
	 * @param S3ClientInterface $client The S3 client instance.
	 * @param array $settings Array of S3 settings.
	 */
	public function configure_stream_wrapper( S3ClientInterface $client, array $settings ): void {
		S3_Media_Sync_Stream_Wrapper::register( $client );
		$object_acl = isset( $settings['object_acl'] ) ? sanitize_text_field( $settings['object_acl'] ) : 'public-read';
		stream_context_set_option( stream_context_get_default(), 's3', 'ACL', $object_acl );
		stream_context_set_option( stream_context_get_default(), 's3', 'seekable', true );
	}
} 
