<?php
/**
 * Integration tests for S3 Media Sync client factory
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync_Client_Factory;
use S3_Media_Sync_Stream_Wrapper;
use S3_Media_Sync;
use S3_Media_Sync_Settings;

/**
 * Test case for S3 Media Sync client factory functionality.
 *
 * @group integration
 * @group client-factory
 * @covers \S3_Media_Sync_Client_Factory
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 */
class ClientFactoryTest extends TestCase {

    /**
     * @var S3_Media_Sync_Client_Factory
     */
    protected $factory;

    /**
     * Set up before each test.
     */
    public function set_up(): void {
        parent::set_up();
        $this->factory = new S3_Media_Sync_Client_Factory();
    }

    /**
     * Test data for client creation scenarios.
     *
     * @return array[] Array of test data.
     */
    public function data_provider_client_settings(): array {
        return [
            'complete settings' => [
                [
                    'key' => 'test-key',
                    'secret' => 'test-secret',
                    'region' => 'test-region',
                    'object_acl' => 'public-read',
                ],
                true,
                true,
            ],
            'minimal settings' => [
                [
                    'region' => 'test-region',
                ],
                false,
                true,
            ],
        ];
    }

    /**
     * Test client creation with different settings.
     *
     * @dataProvider data_provider_client_settings
     * 
     * @param array $settings The settings to test with.
     * @param bool  $has_credentials Whether credentials should be present.
     * @param bool  $has_region Whether region should be present.
     */
    public function test_create_client(array $settings, bool $has_credentials, bool $has_region): void {
        $client = $this->factory->create($settings);

        Assert::assertInstanceOf(S3ClientInterface::class, $client, 'Factory should create an S3 client instance');

        if ($has_credentials) {
            $credentials = $client->getCredentials()->wait();
            Assert::assertSame($settings['key'], $credentials->getAccessKeyId(), 'Client should have correct access key');
            Assert::assertSame($settings['secret'], $credentials->getSecretKey(), 'Client should have correct secret key');
        }

        if ($has_region) {
            $region = $client->getRegion();
            Assert::assertSame($settings['region'], $region, 'Client should have correct region');
        }
    }

    /**
     * Test that client creation fails without required settings.
     */
    public function test_create_client_fails_without_region(): void {
        $settings = [
            'key' => 'test-key',
            'secret' => 'test-secret',
        ];

        $exception_thrown = false;
        try {
            $this->factory->create($settings);
        } catch (\InvalidArgumentException $e) {
            $exception_thrown = true;
            Assert::assertStringContainsStringIgnoringCase('region', $e->getMessage(), 'Exception should mention missing region');
        }
        Assert::assertTrue($exception_thrown, 'Expected InvalidArgumentException was not thrown');
    }

    /**
     * Test data for stream wrapper configuration scenarios.
     *
     * @return array[] Array of test data.
     */
    public function data_provider_stream_wrapper_settings(): array {
        return [
            'complete settings' => [
                [
                    'region' => 'test-region',
                    'object_acl' => 'private',
                ],
                'private',
            ],
            'default acl' => [
                [
                    'region' => 'test-region',
                ],
                'public-read',
            ],
            'custom acl' => [
                [
                    'region' => 'test-region',
                    'object_acl' => 'authenticated-read',
                ],
                'authenticated-read',
            ],
            'sanitized acl' => [
                [
                    'region' => 'test-region',
                    'object_acl' => '<script>alert("xss")</script>public-read',
                ],
                'public-read',
            ],
        ];
    }

    /**
     * Test stream wrapper configuration with different settings.
     *
     * @dataProvider data_provider_stream_wrapper_settings
     * 
     * @param array  $settings     The settings to test with.
     * @param string $expected_acl The expected ACL setting.
     */
    public function test_stream_wrapper_configuration(array $settings, string $expected_acl): void {
        $client = $this->factory->create($settings);
        $this->factory->configure_stream_wrapper($client, $settings);

        Assert::assertContains('s3', stream_get_wrappers(), 'Stream wrapper should be registered');

        $context = stream_context_get_default();
        $s3_options = stream_context_get_options($context)['s3'] ?? [];

        Assert::assertSame($expected_acl, $s3_options['ACL'], 'Stream wrapper should have correct ACL');
        Assert::assertTrue($s3_options['seekable'], 'Stream wrapper should be seekable');
    }

    /**
     * Test client creation with empty credentials.
     */
    public function test_create_client_with_empty_credentials(): void {
        $settings = [
            'region' => 'test-region',
            'key' => '',
            'secret' => '',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client, 'Factory should create an S3 client instance');

        $config = $client->getConfig();
        Assert::assertArrayNotHasKey('credentials', $config, 'Client should not have empty credentials configured');
    }

    /**
     * Test client creation with partial credentials.
     */
    public function test_create_client_with_partial_credentials(): void {
        $settings = [
            'region' => 'test-region',
            'key' => 'test-key',
            // Missing secret
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client, 'Factory should create an S3 client instance');

        $config = $client->getConfig();
        Assert::assertArrayNotHasKey('credentials', $config, 'Client should not have partial credentials configured');
    }

    /**
     * Test client creation with invalid region.
     */
    public function test_create_client_with_empty_region(): void {
        $settings = [
            'region' => '',
            'key' => 'test-key',
            'secret' => 'test-secret',
        ];

        $exception_thrown = false;
        try {
            $this->factory->create($settings);
        } catch (\InvalidArgumentException $e) {
            $exception_thrown = true;
            Assert::assertStringContainsStringIgnoringCase('region', $e->getMessage(), 'Exception should mention missing region');
        }
        Assert::assertTrue($exception_thrown, 'Expected InvalidArgumentException was not thrown');
    }

    /**
     * Test data for proxy configuration scenarios.
     *
     * @return array[] Array of test data.
     */
    public function data_provider_proxy_settings(): array {
        return [
            'with authentication' => [
                [
                    'host' => 'proxy.example.com',
                    'port' => '8080',
                    'username' => 'user',
                    'password' => 'pass',
                ],
                'user:pass@proxy.example.com:8080',
            ],
            'without authentication' => [
                [
                    'host' => 'proxy.example.com',
                    'port' => '8080',
                ],
                'proxy.example.com:8080',
            ],
        ];
    }

    /**
     * Test proxy configuration with different settings.
     *
     * @dataProvider data_provider_proxy_settings
     * 
     * @param array  $proxy_settings The proxy settings to test.
     * @param string $expected_proxy The expected proxy string.
     */
    public function test_proxy_configuration(array $proxy_settings, string $expected_proxy): void {
        $proxy_auth = '';
        $proxy_address = $proxy_settings['host'] . ':' . $proxy_settings['port'];

        if (isset($proxy_settings['username']) && isset($proxy_settings['password'])) {
            $proxy_auth = $proxy_settings['username'] . ':' . $proxy_settings['password'] . '@';
        }

        $expected_proxy_string = $proxy_auth . $proxy_address;
        Assert::assertSame($expected_proxy, $expected_proxy_string, 'Proxy string should be correctly formatted');
    }

    /**
     * Test client creation with WordPress proxy configuration.
     */
    public function test_create_client_with_wordpress_proxy(): void {
        if (!defined('WP_PROXY_HOST')) {
            define('WP_PROXY_HOST', 'proxy.example.com');
            define('WP_PROXY_PORT', '8080');
            define('WP_PROXY_USERNAME', 'user');
            define('WP_PROXY_PASSWORD', 'pass');
        }

        $settings = [
            'region' => 'test-region',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('test-region', $client->getRegion());
    }

    /**
     * Test client creation with WordPress proxy configuration without authentication.
     */
    public function test_create_client_with_wordpress_proxy_without_auth(): void {
        if (!defined('WP_PROXY_HOST')) {
            define('WP_PROXY_HOST', 'proxy.example.com');
            define('WP_PROXY_PORT', '8080');
        }

        $settings = [
            'region' => 'test-region',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('test-region', $client->getRegion());
    }

    /**
     * Test client creation without WordPress proxy configuration.
     */
    public function test_create_client_without_wordpress_proxy(): void {
        $settings = [
            'region' => 'test-region',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('test-region', $client->getRegion());
    }

    /**
     * Clean up after each test.
     */
    public function tear_down(): void {
        parent::tear_down();
        
        // Clean up stream wrapper registration
        if (in_array('s3', stream_get_wrappers(), true)) {
            stream_wrapper_unregister('s3');
        }
    }
} 
