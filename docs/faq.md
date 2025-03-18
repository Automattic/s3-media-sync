# Frequently Asked Questions

## How can I upload media to a subdirectory in S3?

As an example, you already have a bucket named `my-awesome-site` but you want all of your media to go into a `preprod` subdirectory of that bucket. To configure media to upload to that subdirectory, go to the S3 Media Sync settings page and enter the following for the `S3 Bucket Name` field:

```
my-awesome-site/preprod
```

Then, all media will automatically be kept in-sync within `my-awesome-site/preprod/wp-content/uploads`. 

## How can I confirm if all of the attachments were uploaded?

You can check which attachments were skipped by running the following command:

```sh
wp vip migration validate-attachments invalid-attachments.csv --url=example-site.com
```

The generated log file will be available at `invalid-attachments.csv`. The full command can be found in the [VIP Go MU plugins repository](https://github.com/Automattic/vip-go-mu-plugins/blob/master/wp-cli/vip-migrations.php#L165-L187).

## How do I troubleshoot AWS access issues?

If you're experiencing issues connecting to your S3 bucket:

1. Verify your AWS credentials are correct
2. Ensure your IAM user has the correct permissions as outlined in the [AWS Setup Guide](aws-setup-guide.md)
3. Check that your bucket name and region are entered correctly
4. Use the "Test S3 Access" button on the settings page to diagnose connection issues 

## How does the plugin handle deleted media files?

When you delete a media file in WordPress, S3 Media Sync automatically deletes that file from your S3 bucket as well. This includes:

1. The original file
2. All generated thumbnails and image sizes
3. Any related files with similar filenames in the same directory

This keeps your S3 bucket in sync with your WordPress media library and prevents orphaned files from accumulating in your bucket. The deletion happens immediately when you delete media through the WordPress media library.

If you need to manually remove files from S3, you can also use the WP-CLI command:
```sh
wp s3-media rm path/to/file.jpg
```
