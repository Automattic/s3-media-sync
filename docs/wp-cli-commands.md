# WP-CLI Commands

S3 Media Sync provides several WP-CLI commands for managing media uploads to S3.

## Upload a Single Attachment

To upload a single attachment to S3, use the following command:

```sh
wp s3-media upload <attachment_id>
```

**Example:**

```sh
wp s3-media upload 123
```

## Upload All Validated Media

To upload all validated media to S3, use the command:

```sh
wp s3-media upload-all [--threads=<number>]
```

**Options:**
- `--threads=<number>`: The number of concurrent threads to use for uploading. Defaults to 10 (range: 1-10).

**Example:**

```sh
wp s3-media upload-all --threads=5
```

## Remove Files from S3

There are two ways to remove files from S3:

1. **Automatic deletion**: When you delete a media file through the WordPress media library, S3 Media Sync automatically removes the file and all of its associated thumbnails from S3.

2. **Manual deletion**: If you need to manually remove files from S3 without deleting them from WordPress, or to clean up orphaned files, use this command:

```sh
wp s3-media rm <path> [--regex=<regex>]
```

**Options:**
- `<path>`: The path of the file or directory to remove from S3.
- `--regex=<regex>`: Optional regex pattern to match files for deletion.

**Example:**

```sh
# Remove a specific file
wp s3-media rm wp-content/uploads/2023/04/image.jpg

# Remove all .tmp files in a directory
wp s3-media rm wp-content/uploads/2023/04/ --regex="/\.tmp$/"
```

> **Note**: The manual deletion does not affect files in your WordPress media library, only in S3. 
