<?php

namespace S3_Media_Sync\Value_Objects;

/**
 * Value object representing a WordPress media attachment.
 */
class WordPress_Attachment {
    /**
     * @var int The attachment ID
     */
    private int $id;

    /**
     * @var Local_File The main attachment file
     */
    private Local_File $file;

    /**
     * @var Local_File[] Array of thumbnail files
     */
    private array $thumbnails;

    /**
     * @var array The attachment metadata
     */
    private array $metadata;

    /**
     * Create a new attachment instance
     */
    private function __construct(
        int $id,
        Local_File $file,
        array $thumbnails,
        array $metadata
    ) {
        $this->id = $id;
        $this->file = $file;
        $this->thumbnails = $thumbnails;
        $this->metadata = $metadata;
    }

    /**
     * Create an attachment from a WordPress post ID
     * 
     * @throws \InvalidArgumentException If the post does not exist or is not an attachment
     */
    public static function from_post_id(int $post_id): self {
        // Check if post exists and is an attachment
        $post = get_post($post_id);
        if (!$post) {
            throw new \InvalidArgumentException('Post not found: ' . $post_id);
        }
        if ($post->post_type !== 'attachment') {
            throw new \InvalidArgumentException('Post is not an attachment: ' . $post_id);
        }

        $upload_dir = wp_upload_dir();
        $file_path = get_attached_file($post_id);
        $metadata = wp_get_attachment_metadata($post_id);

        // Validate file path
        if (!$file_path || !file_exists($file_path)) {
            throw new \InvalidArgumentException('Attachment file not found: ' . $file_path);
        }

        // Create the main file object
        $main_file = Local_File::from_path($file_path);

        // Get thumbnail files
        $thumbnails = [];
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $info) {
                $thumb_path = str_replace(
                    basename($file_path),
                    $info['file'],
                    $file_path
                );
                if (file_exists($thumb_path)) {
                    $thumbnails[$size] = Local_File::from_metadata(
                        path: $thumb_path,
                        mime_type: $info['mime-type'],
                        size: isset($info['filesize']) ? (int) $info['filesize'] : null,
                        md5_hash: null
                    );
                }
            }
        }

        return new self(
            id: $post_id,
            file: $main_file,
            thumbnails: $thumbnails,
            metadata: $metadata ?: []
        );
    }

    /**
     * Get the attachment ID
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Get the main attachment file
     */
    public function get_file(): Local_File {
        return $this->file;
    }

    /**
     * Get all thumbnail files
     *
     * @return Local_File[]
     */
    public function get_thumbnails(): array {
        return $this->thumbnails;
    }

    /**
     * Get a specific thumbnail file
     */
    public function get_thumbnail(string $size): ?Local_File {
        return $this->thumbnails[$size] ?? null;
    }

    /**
     * Get the attachment metadata
     */
    public function get_metadata(): array {
        return $this->metadata;
    }

    /**
     * Get the attachment's title
     */
    public function get_title(): string {
        return get_the_title($this->id);
    }

    /**
     * Get the attachment's alt text
     */
    public function get_alt_text(): string {
        return get_post_meta($this->id, '_wp_attachment_image_alt', true) ?: '';
    }

    /**
     * Get the attachment's caption
     */
    public function get_caption(): string {
        $post = get_post($this->id);
        return $post ? $post->post_excerpt : '';
    }

    /**
     * Get the attachment's description
     */
    public function get_description(): string {
        $post = get_post($this->id);
        return $post ? $post->post_content : '';
    }

    /**
     * Check if this attachment matches another attachment
     */
    public function equals(WordPress_Attachment $other): bool {
        return $this->id === $other->id;
    }
} 
