<?php
/**
 * Integration tests for Client Factory class
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration;

use Aws\S3\S3ClientInterface;
use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\S3_Bucket;
use S3_Media_Sync\S3_Media_Sync_Client_Factory;
use S3_Media_Sync;

/**
 * Test case for Client Factory class.
 *
 * @group integration
 * @group client-factory
 * @covers \S3_Media_Sync\S3_Media_Sync_Client_Factory
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 */
class ClientFactoryTest extends TestCase {

    /**
     * @var S3_Media_Sync_Client_Factory
     */
    protected $factory;

    /**
     * @var array<string, mixed>
     */
    protected array $default_settings;

    /**
     * Set up before each test.
     */
    public function set_up(): void {
        parent::set_up();
        $this->factory = new S3_Media_Sync_Client_Factory();
        
        // Set up default test settings
        $this->default_settings = [
            'bucket' => 'test-bucket',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'region' => 'us-east-1',
            'object_acl' => 'public-read',
            'use_acl' => true
        ];
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
                    'bucket' => 'test-bucket',
                    'key' => 'test-key',
                    'secret' => 'test-secret',
                    'region' => 'us-east-1',
                    'object_acl' => 'public-read',
                ],
                true,
                true,
            ],
            'minimal settings' => [
                [
                    'bucket' => 'test-bucket',
                    'region' => 'us-east-1',
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
     * Test that client creation fails without region.
     */
    public function test_create_client_fails_without_region(): void {
        $settings = [
            'bucket' => 'test-bucket',
            'key' => 'test-key',
            'secret' => 'test-secret',
        ];

        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Region_Exception::class);
        $this->expectExceptionMessage('Invalid region: . Must be one of:');
        $this->factory->create($settings);
    }

    /**
     * Test client creation with empty region.
     */
    public function test_create_client_with_empty_region(): void {
        $settings = [
            'bucket' => 'test-bucket',
            'region' => '',
            'key' => 'test-key',
            'secret' => 'test-secret',
        ];

        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Region_Exception::class);
        $this->expectExceptionMessage('Invalid region: . Must be one of:');
        $this->factory->create($settings);
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
                    'bucket' => 'test-bucket',
                    'region' => 'us-east-1',
                    'object_acl' => 'private',
                ],
                'private',
            ],
            'default acl' => [
                [
                    'bucket' => 'test-bucket',
                    'region' => 'us-east-1',
                ],
                'public-read',
            ],
            'custom acl' => [
                [
                    'bucket' => 'test-bucket',
                    'region' => 'us-east-1',
                    'object_acl' => 'authenticated-read',
                ],
                'authenticated-read',
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
        $bucket = S3_Bucket::from_settings($settings);
        $this->factory->configure_stream_wrapper($client, $bucket);

        Assert::assertContains('s3', stream_get_wrappers(), 'Stream wrapper should be registered');

        $context = stream_context_get_default();
        $s3_options = stream_context_get_options($context)['s3'] ?? [];

        Assert::assertSame($expected_acl, $s3_options['ACL'], 'Stream wrapper should have correct ACL');
        Assert::assertTrue($s3_options['seekable'], 'Stream wrapper should be seekable');
    }

    /**
     * Test stream wrapper configuration with unsanitized ACL.
     *
     * @dataProvider data_provider_stream_wrapper_settings
     * 
     * @param array  $settings     The settings to test with.
     * @param string $expected_acl The expected ACL setting.
     */
    public function test_stream_wrapper_configuration_with_unsanitized_acl(array $settings): void {
        $settings['object_acl'] = '<script>alert("xss")</script>public-read';
        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_Bucket_Exception::class);
        $this->expectExceptionMessage('Invalid ACL value:');
        
        $client = $this->factory->create($settings);
        $bucket = S3_Bucket::from_settings($settings);
        $this->factory->configure_stream_wrapper($client, $bucket);
    }

    /**
     * Test client creation with empty credentials.
     */
    public function test_create_client_with_empty_credentials(): void {
        $settings = [
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
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
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
            'key' => 'test-key',
            // Missing secret
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client, 'Factory should create an S3 client instance');

        $config = $client->getConfig();
        Assert::assertArrayNotHasKey('credentials', $config, 'Client should not have partial credentials configured');
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
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('us-east-1', $client->getRegion());
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
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('us-east-1', $client->getRegion());
    }

    /**
     * Test client creation without WordPress proxy configuration.
     */
    public function test_create_client_without_wordpress_proxy(): void {
        $settings = [
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
        ];

        $client = $this->factory->create($settings);
        Assert::assertInstanceOf(S3ClientInterface::class, $client);

        // Test that the client was created with the correct region
        Assert::assertSame('us-east-1', $client->getRegion());
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
