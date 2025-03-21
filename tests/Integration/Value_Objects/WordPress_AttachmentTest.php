<?php
/**
 * Integration tests for WordPress_Attachment value object
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration\Value_Objects;

use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\WordPress_Attachment;
use S3_Media_Sync\Value_Objects\Local_File;

/**
 * Test case for WordPress_Attachment value object.
 *
 * @covers \S3_Media_Sync\Value_Objects\WordPress_Attachment
 * @uses \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync_Stream_Wrapper
 * @uses \S3_Media_Sync\Exceptions\Invalid_File_Exception
 * @group integration
 * @group value-objects
 */
class WordPress_AttachmentTest extends TestCase {

    protected $test_image;
    protected $attachment_id;
    protected $uploaded_file_name;

    public function set_up(): void {
        parent::set_up();

        // Create a test image file
        $this->test_image = $this->create_temp_file('test-image.jpg', $this->create_test_image());

        // Create a proper WordPress attachment
        $file_array = [
            'name' => 'test-image.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $this->test_image->get_path(),
            'error' => 0,
            'size' => filesize($this->test_image->get_path())
        ];

        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $this->attachment_id = media_handle_sideload($file_array, 0);
        if (is_wp_error($this->attachment_id)) {
            throw new \RuntimeException($this->attachment_id->get_error_message());
        }

        // Get the actual uploaded filename
        $this->uploaded_file_name = basename(get_attached_file($this->attachment_id));
    }

    /**
     * Test creating a WordPress_Attachment from a post ID
     */
    public function test_from_post_id(): void {
        $attachment = WordPress_Attachment::from_post_id($this->attachment_id);

        Assert::assertInstanceOf(WordPress_Attachment::class, $attachment);
        Assert::assertInstanceOf(Local_File::class, $attachment->get_file());
        Assert::assertSame($this->attachment_id, $attachment->get_id());
        Assert::assertSame($this->uploaded_file_name, basename($attachment->get_file()->get_path()));
    }

    /**
     * Test getting attachment metadata
     */
    public function test_get_metadata(): void {
        $attachment = WordPress_Attachment::from_post_id($this->attachment_id);
        $metadata = $attachment->get_metadata();

        Assert::assertIsArray($metadata);
        Assert::assertArrayHasKey('file', $metadata);
        Assert::assertArrayHasKey('width', $metadata);
        Assert::assertArrayHasKey('height', $metadata);
        Assert::assertSame($this->uploaded_file_name, basename($metadata['file']));
    }

    /**
     * Test getting thumbnail files
     */
    public function test_get_thumbnail_files(): void {
        $attachment = WordPress_Attachment::from_post_id($this->attachment_id);
        $metadata = $attachment->get_metadata();

        // Get thumbnail files from metadata
        $thumbnails = [];
        if (!empty($metadata['sizes'])) {
            $upload_dir = wp_upload_dir();
            $file_dir = dirname($metadata['file']);
            foreach ($metadata['sizes'] as $size => $info) {
                $thumbnail_path = path_join($upload_dir['basedir'], path_join($file_dir, $info['file']));
                $thumbnails[] = Local_File::from_path($thumbnail_path);
            }
        }

        Assert::assertIsArray($thumbnails);
        // WordPress generates several thumbnail sizes by default
        Assert::assertNotEmpty($thumbnails);

        foreach ($thumbnails as $thumbnail) {
            Assert::assertInstanceOf(Local_File::class, $thumbnail);
            Assert::assertTrue(file_exists($thumbnail->get_path()));
        }
    }

    /**
     * Test invalid post ID
     */
    public function test_invalid_post_id(): void {
        try {
            WordPress_Attachment::from_post_id(999999);
            Assert::fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            Assert::assertStringContainsString('Post not found', $e->getMessage());
        }
    }

    /**
     * Test non-attachment post type
     */
    public function test_non_attachment_post_type(): void {
        // Create a regular post
        $post_id = wp_insert_post([
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish'
        ]);

        try {
            WordPress_Attachment::from_post_id($post_id);
            Assert::fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            Assert::assertStringContainsString('Post is not an attachment', $e->getMessage());
        }
    }

    /**
     * Creates a test image
     */
    protected function create_test_image(): string {
        // Create a larger image (1024x768) to ensure WordPress generates thumbnails
        $image = imagecreatetruecolor(1024, 768);
        
        // Fill with a gradient to make it more realistic
        for ($i = 0; $i < 1024; $i++) {
            $color = imagecolorallocate($image, (int)($i / 4), 100, 100);
            imagefilledrectangle($image, $i, 0, $i, 768, $color);
        }

        ob_start();
        imagejpeg($image, null, 90); // Higher quality for better thumbnail generation
        $contents = ob_get_clean();
        imagedestroy($image);
        return $contents;
    }

    public function tear_down(): void {
        // Clean up the attachment
        if ($this->attachment_id) {
            wp_delete_attachment($this->attachment_id, true);
        }

        // Clean up the test image
        if ($this->test_image && file_exists($this->test_image->get_path())) {
            unlink($this->test_image->get_path());
        }

        parent::tear_down();
    }
} 
