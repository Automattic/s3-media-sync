<?php
/**
 * Integration tests for Local_File value object
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests\Integration\Value_Objects;

use PHPUnit\Framework\Assert;
use S3_Media_Sync\Tests\TestCase;
use S3_Media_Sync\Value_Objects\Local_File;
use S3_Media_Sync\Exceptions\Invalid_File_Exception;

/**
 * Test case for Local_File value object.
 *
 * @covers \S3_Media_Sync\Value_Objects\Local_File
 * @uses \S3_Media_Sync
 * @uses \S3_Media_Sync_Settings
 * @uses \S3_Media_Sync\Value_Objects\Region
 * @uses \S3_Media_Sync\Value_Objects\S3_Bucket
 * @uses \S3_Media_Sync\Value_Objects\S3_File
 * @uses \S3_Media_Sync\Value_Objects\WordPress_Attachment
 * @uses \S3_Media_Sync\Exceptions\Invalid_File_Exception
 * @group integration
 * @group value-objects
 */
class Local_FileTest extends TestCase {

    protected $test_file_path;
    protected $test_content = 'Test content';

    public function set_up(): void {
        parent::set_up();

        // Create a test file
        $upload_dir = wp_upload_dir();
        $this->test_file_path = $upload_dir['path'] . '/test-file.txt';
        file_put_contents($this->test_file_path, $this->test_content);
    }

    /**
     * Test creating a Local_File from a path
     */
    public function test_from_path(): void {
        $local_file = Local_File::from_path($this->test_file_path);

        Assert::assertInstanceOf(Local_File::class, $local_file);
        Assert::assertSame(realpath($this->test_file_path), realpath($local_file->get_path()));
        Assert::assertSame('test-file.txt', $local_file->get_basename());
        Assert::assertSame($this->test_content, file_get_contents($local_file->get_path()));
    }

    /**
     * Test getting file basename
     */
    public function test_get_basename(): void {
        $local_file = Local_File::from_path($this->test_file_path);
        Assert::assertSame('test-file.txt', $local_file->get_basename());

        // Test with a path containing directories
        $nested_dir = dirname($this->test_file_path) . '/nested';
        wp_mkdir_p($nested_dir);
        $nested_path = $nested_dir . '/test.txt';
        file_put_contents($nested_path, $this->test_content);
        
        $local_file = Local_File::from_path($nested_path);
        Assert::assertSame('test.txt', $local_file->get_basename());
        unlink($nested_path);
        rmdir($nested_dir);
    }

    /**
     * Test getting file directory
     */
    public function test_get_directory(): void {
        $local_file = Local_File::from_path($this->test_file_path);
        Assert::assertSame(realpath(dirname($this->test_file_path)), realpath($local_file->get_directory()));
    }

    /**
     * Test getting file extension
     */
    public function test_get_extension(): void {
        $local_file = Local_File::from_path($this->test_file_path);
        Assert::assertSame('txt', $local_file->get_extension());

        // Test with no extension
        $no_ext_path = str_replace('.txt', '', $this->test_file_path);
        file_put_contents($no_ext_path, $this->test_content);
        $local_file = Local_File::from_path($no_ext_path);
        Assert::assertSame('', $local_file->get_extension());
        unlink($no_ext_path);

        // Test with multiple dots
        $multi_ext_path = $this->test_file_path . '.backup.gz';
        file_put_contents($multi_ext_path, $this->test_content);
        $local_file = Local_File::from_path($multi_ext_path);
        Assert::assertSame('gz', $local_file->get_extension());
        unlink($multi_ext_path);
    }

    /**
     * Test file existence check
     */
    public function test_exists(): void {
        $local_file = Local_File::from_path($this->test_file_path);
        Assert::assertTrue($local_file->exists());

        // Test non-existent file
        $non_existent_path = $this->test_file_path . '.non-existent';
        $non_existent = Local_File::from_metadata(
            $non_existent_path,
            'text/plain',
            0,
            null
        );
        Assert::assertFalse($non_existent->exists());
    }

    /**
     * Test invalid file path
     */
    public function test_invalid_path(): void {
        $this->expectException(\S3_Media_Sync\Exceptions\Invalid_File_Exception::class);
        $this->expectExceptionMessage('File path cannot be empty');
        Local_File::from_metadata('', 'text/plain', 0, null);
    }

    public function tear_down(): void {
        if (file_exists($this->test_file_path)) {
            unlink($this->test_file_path);
        }
        parent::tear_down();
    }
} 
