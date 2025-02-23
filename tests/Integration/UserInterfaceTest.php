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
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[key]" id="s3_media_sync_settings[key]" value="test-key">', $output);
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
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[secret]" id="s3_media_sync_settings[secret]" value="test-secret">', $output);
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
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[bucket]" id="s3_media_sync_settings[bucket]" value="test-bucket">', $output);
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
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[region]" id="s3_media_sync_settings[region]" value="test-region">', $output);
    }

    public function test_s3_object_acl_render_displays_correct_html() {
        // Test cases for different ACL values
        $test_cases = [
            'private' => [
                'value' => 'private',
                'expected_private' => ' selected=\'selected\'',
                'expected_public' => '',
            ],
            'public-read' => [
                'value' => 'public-read',
                'expected_private' => '',
                'expected_public' => ' selected=\'selected\'',
            ],
            'empty' => [
                'value' => '',
                'expected_private' => '',
                'expected_public' => '',
            ],
        ];

        foreach ($test_cases as $case => $data) {
            // Simulate the options array
            $options = ['object_acl' => $data['value']];

            // Start output buffering
            ob_start();
            $value = !empty($options['object_acl']) ? $options['object_acl'] : '';
            include dirname(\S3_MEDIA_SYNC_FILE) . '/views/s3-object-acl-template.php';
            $output = ob_get_clean();

            // Assert the output contains the expected HTML structure
            $this->assertStringContainsString('<select name="s3_media_sync_settings[object_acl]" id="s3_media_sync_settings[object_acl]">', $output, "Select element not found for case: {$case}");
            
            // Check for private option with correct selected state
            $this->assertMatchesRegularExpression(
                '/<option' . preg_quote($data['expected_private'], '/') . '>\s*private\s*<\/option>/',
                $output,
                "Private option not rendered correctly for case: {$case}"
            );

            // Check for public-read option with correct selected state
            $this->assertMatchesRegularExpression(
                '/<option' . preg_quote($data['expected_public'], '/') . '>\s*public-read\s*<\/option>/',
                $output,
                "Public-read option not rendered correctly for case: {$case}"
            );
        }
    }
} 
