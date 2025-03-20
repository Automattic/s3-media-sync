<?php

namespace S3_Media_Sync\Tests\Integration;

use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync_Settings;

/**
 * Integration tests for UI rendering in S3 Media Sync.
 *
 * @package S3_Media_Sync
 * @group integration
 * @covers \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync
 */
class UserInterfaceTest extends TestCase {

    /**
     * @var S3_Media_Sync_Settings
     */
    protected $settings_handler;

    /**
     * @var S3_Media_Sync_Settings
     */
    protected $interface;

    public function set_up(): void {
        parent::set_up();
        $this->settings_handler = new S3_Media_Sync_Settings();
        $this->interface = $this->settings_handler;
        $this->settings_handler->init();
    }

    public function test_s3_key_render_displays_correct_html() {
        // Simulate the options array and update settings
        $options = ['key' => 'test-key'];
        $this->settings_handler->update_settings($options);

        // Start output buffering
        ob_start();
        $this->settings_handler->s3_key_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[key]" id="s3_media_sync_settings[key]" value="test-key">', $output);
    }

    public function test_s3_secret_render_displays_correct_html() {
        // Simulate the options array and update settings
        $options = ['secret' => 'test-secret'];
        $this->settings_handler->update_settings($options);

        // Start output buffering
        ob_start();
        $this->settings_handler->s3_secret_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[secret]" id="s3_media_sync_settings[secret]" value="test-secret">', $output);
    }

    public function test_s3_bucket_render_displays_correct_html() {
        // Simulate the options array and update settings
        $options = ['bucket' => 'test-bucket'];
        $this->settings_handler->update_settings($options);

        // Start output buffering
        ob_start();
        $this->settings_handler->s3_bucket_render();
        $output = ob_get_clean();

        // Assert the output contains the expected HTML
        $this->assertStringContainsString('<input type="text" name="s3_media_sync_settings[bucket]" id="s3_media_sync_settings[bucket]" value="test-bucket">', $output);
    }

    public function test_s3_region_render_displays_correct_html() {
        $settings = new S3_Media_Sync_Settings();
        $settings->update_settings([
            'region' => 'us-east-1'
        ]);

        ob_start();
        $settings->s3_region_render();
        $output = ob_get_clean();

        // Check for select element with correct name and ID
        $this->assertStringContainsString(
            '<select name="s3_media_sync_settings[region]" id="s3_media_sync_settings[region]"',
            $output
        );

        // Check that us-east-1 is selected
        $this->assertStringContainsString(
            '<option value="us-east-1" selected=\'selected\'>us-east-1',
            $output
        );

        // Check that the description is present
        $this->assertStringContainsString(
            'Select the AWS region where your bucket is located.',
            $output
        );
    }

    public function test_s3_object_acl_render_displays_correct_html() {
        // Test cases for different ACL values
        $test_cases = [
            'private' => [
                'value' => 'private',
                'expected_pattern' => '/<option[^>]*>\s*private\s*<\/option>.*<option[^>]*selected=\'selected\'>\s*public-read\s*<\/option>/s',
            ],
            'public-read' => [
                'value' => 'public-read',
                'expected_pattern' => '/<option[^>]*>\s*private\s*<\/option>.*<option[^>]*selected=\'selected\'>\s*public-read\s*<\/option>/s',
            ],
            'empty' => [
                'value' => '',
                'expected_pattern' => '/<option[^>]*>\s*private\s*<\/option>.*<option[^>]*selected=\'selected\'>\s*public-read\s*<\/option>/s',
            ],
        ];

        foreach ($test_cases as $case => $data) {
            // Simulate the options array and update settings
            $options = ['object_acl' => $data['value']];
            $this->settings_handler->update_settings($options);

            // Start output buffering
            ob_start();
            $this->settings_handler->s3_object_acl_render();
            $output = ob_get_clean();

            // Assert the output contains the expected HTML structure
            $this->assertStringContainsString(
                '<select name="s3_media_sync_settings[object_acl]" id="s3_media_sync_settings[object_acl]">',
                $output,
                "Select element not found for case: {$case}"
            );
            
            // Check for correct option selection
            $this->assertMatchesRegularExpression(
                $data['expected_pattern'],
                $output,
                "Options not rendered correctly for case: {$case}"
            );
        }
    }

    public function test_settings_page_displays_current_settings(): void {
        $options = [
            'region' => 'us-east-1',
            'bucket' => 'test-bucket',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'object_acl' => 'public-read',
            'use_acl' => true
        ];
        
        // Update settings and ensure they're registered
        update_option('s3_media_sync_settings', $options);
        
        // Initialize settings and register settings screen
        $this->settings_handler->init();
        $this->settings_handler->settings_screen_init();

        ob_start();
        $this->interface->render_settings_page();
        $output = ob_get_clean();

        // Test for each setting field
        $expected_fields = [
            'region' => 'us-east-1',
            'bucket' => 'test-bucket',
            'key' => 'test-key',
            'secret' => 'test-secret'
        ];

        foreach ($expected_fields as $field => $value) {
            $field_name = sprintf('name="s3_media_sync_settings[%s]"', $field);
            $field_value = sprintf('value="%s"', $value);
            
            $this->assertStringContainsString(
                $field_name,
                $output,
                sprintf('%s field name not found in output: %s', ucfirst($field), $output)
            );
            $this->assertStringContainsString(
                $field_value,
                $output,
                sprintf('%s value not found in output: %s', ucfirst($field), $output)
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
