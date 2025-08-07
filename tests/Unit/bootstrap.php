<?php

/**
 * Unit test bootstrap for S3 Media Sync
 * 
 * This file provides minimal WordPress function polyfills for unit testing
 * without requiring a full WordPress installation.
 */

// Autoload dependencies
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// WordPress function polyfills for unit tests
if (!function_exists('trailingslashit')) {
    /**
     * Appends a trailing slash.
     *
     * @param string $string What to add the trailing slash to.
     * @return string String with trailing slash added.
     */
    function trailingslashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    /**
     * Removes trailing forward slashes and backslashes if they exist.
     *
     * @param string $string What to remove the trailing slashes from.
     * @return string String without the trailing slashes.
     */
    function untrailingslashit($string) {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('wp_upload_dir')) {
    /**
     * Mock wp_upload_dir for testing
     */
    function wp_upload_dir() {
        return [
            'path' => '/tmp/uploads',
            'url' => 'https://example.com/uploads',
            'basedir' => '/tmp',
            'baseurl' => 'https://example.com',
        ];
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * Mock get_post_meta for testing
     */
    function get_post_meta($post_id, $key, $single = false) {
        return $single ? '' : [];
    }
}

if (!function_exists('wp_get_attachment_metadata')) {
    /**
     * Mock wp_get_attachment_metadata for testing
     */
    function wp_get_attachment_metadata($post_id) {
        return [];
    }
}