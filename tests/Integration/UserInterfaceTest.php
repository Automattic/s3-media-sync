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
        // Simulate the options array and update WordPress option
        $options = ['key' => 'test-key'];
        update_option('s3_media_sync_settings', $options);

        // Start output buffering
        ob_start();
        $this->s3_media_sync->s3_key_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[key]" id="s3_media_sync_settings[key]" value="test-key">', $output);
    }

    public function test_s3_secret_render_displays_correct_html() {
        // Simulate the options array and update WordPress option
        $options = ['secret' => 'test-secret'];
        update_option('s3_media_sync_settings', $options);

        // Start output buffering
        ob_start();
        $this->s3_media_sync->s3_secret_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[secret]" id="s3_media_sync_settings[secret]" value="test-secret">', $output);
    }

    public function test_s3_bucket_render_displays_correct_html() {
        // Simulate the options array and update WordPress option
        $options = ['bucket' => 'test-bucket'];
        update_option('s3_media_sync_settings', $options);

        // Start output buffering
        ob_start();
        $this->s3_media_sync->s3_bucket_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[bucket]" id="s3_media_sync_settings[bucket]" value="test-bucket">', $output);
    }

    public function test_s3_region_render_displays_correct_html() {
        // Simulate the options array and update WordPress option
        $options = ['region' => 'test-region'];
        update_option('s3_media_sync_settings', $options);

        // Start output buffering
        ob_start();
        $this->s3_media_sync->s3_region_render();
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
            // Simulate the options array and update WordPress option
            $options = ['object_acl' => $data['value']];
            update_option('s3_media_sync_settings', $options);

            // Start output buffering
            ob_start();
            $this->s3_media_sync->s3_object_acl_render();
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

    /**
     * Clean up after each test.
     */
    public function tear_down(): void {
        parent::tear_down();
        delete_option('s3_media_sync_settings');
    }
} 
