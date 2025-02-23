<?php

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync\Tests\TestCase;

/**
 * Integration tests for UI rendering in S3 Media Sync.
 *
 * @package S3_Media_Sync
 * @group integration
 * @covers S3_Media_Sync
 */
class UserInterfaceTest extends TestCase {

    public function test_s3_key_render_displays_correct_html() {
        // Simulate the options array
        $options = ['key' => 'test-key'];

        // Start output buffering
        ob_start();
        $value = !empty($options['key']) ? $options['key'] : '';
        include dirname(\S3_MEDIA_SYNC_FILE) . '/views/s3-key-template.php';
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[key]" value="test-key">', $output);
    }

    public function test_s3_secret_render_displays_correct_html() {
        // Simulate the options array
        $options = ['secret' => 'test-secret'];

        // Start output buffering
        ob_start();
        $value = !empty($options['secret']) ? $options['secret'] : '';
        include dirname(\S3_MEDIA_SYNC_FILE) . '/views/s3-secret-template.php';
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[secret]" value="test-secret">', $output);
    }

    public function test_s3_bucket_render_displays_correct_html() {
        // Simulate the options array
        $options = ['bucket' => 'test-bucket'];

        // Start output buffering
        ob_start();
        $value = !empty($options['bucket']) ? $options['bucket'] : '';
        include dirname(\S3_MEDIA_SYNC_FILE) . '/views/s3-bucket-template.php';
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[bucket]" value="test-bucket">', $output);
    }

    public function test_s3_region_render_displays_correct_html() {
        // Simulate the options array
        $options = ['region' => 'test-region'];

        // Start output buffering
        ob_start();
        $value = !empty($options['region']) ? $options['region'] : '';
        include dirname(\S3_MEDIA_SYNC_FILE) . '/views/s3-region-template.php';
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[region]" value="test-region">', $output);
    }
} 
