# S3 Media Sync

This plugin syncs the `uploads` directory of a VIP Platform WordPress environment to an AWS S3 instance.

Props to [S3-Uploads](https://github.com/humanmade/S3-Uploads/) and [Human Made](https://hmn.md/) for creating much of the functionality: https://github.com/humanmade/S3-Uploads

## Setup

### Build the plugin

This plugin uses [composer](https://getcomposer.org/) as a package manager. After downloading the plugin (as a ZIP file or via `git pull`) run one of the following commands:

* For production: `composer install --no-dev --optimize-autoloader` 
* For development: `composer install` 

Running one of the above commands will create a `vendor` directory which is required for the plugin to function correctly. Applications that are using CI/CD already run one of these commands automatically and can skip this step.

### Activate the plugin

* [Commit the plugin](https://docs.wpvip.com/technical-references/installing-plugins-best-practices/) to your application's `plugins` directory.
* Activate the plugin through code or within the WordPress Admin dashboard.
* [Create an IAM user with Programmatic Access](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_users_create.html).
* Enter the provided AWS S3 API keys on the plugins's Settings page.
* Backfill the uploads directory on AWS by running the following command: 

```sh
wp s3-media upload-all --url=example-site.com
```

## WP-CLI Commands

This plugin provides several WP-CLI commands for managing media uploads to S3.

### Upload a Single Attachment

To upload a single attachment to S3, use the following command:

```sh
wp s3-media upload <attachment_id>
```

**Example:**

```sh
wp s3-media upload 123
```

### Upload All Validated Media

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

### Remove Files from S3

To remove files from S3, use the command:

```sh
wp s3-media rm <path> [--regex=<regex>]
```

**Options:**
- `<path>`: The path of the file or directory to remove from S3.
- `--regex=<regex>`: Optional regex pattern to match files for deletion.

**Example:**

```sh
wp s3-media rm path/to/file.jpg
```

## Development

### Running Tests

This plugin uses PHPUnit for testing. To run the test suite:

1. Install development dependencies:
```sh
composer install
```

2. Install the WordPress test suite:
```sh
./bin/install-wp-tests.sh s3_media_sync_test root 'root' localhost latest
```

The install script parameters are:
- Database name: `s3_media_sync_test`
- Database user: `root`
- Database password: `` (empty)
- Database host: `localhost`
- WordPress version: `latest`

3. Run tests:
```sh
composer test
```

For specific test files:
```sh
composer test -- tests/test-class-s3-media-sync-wp-cli.php
```

For coverage reports:
```sh
composer test -- --coverage-html coverage
```

## FAQ

*How can I upload media to a subdirectory in S3?*

As an example, you already have a bucket named `my-awesome-site` but you want all of your media to go into a `preprod` subdirectory of that bucket. To configure media to upload to that subdirectory, go to the S3 Media Sync settings page and enter the following for the `S3 Bucket Name` field:

```
my-awesome-site/preprod
```

Then, all media will automatically be kept in-sync within `my-awesome-site/preprod/wp-content/uploads`. 

*How can I confirm if all of the attachments were uploaded?*

You can check which attachments were skipped by running the following command:

```sh
wp vip migration validate-attachments invalid-attachments.csv --url=example-site.com
```

The generated log file will be available at `invalid-attachments.csv`. The full command can be found here:

https://github.com/Automattic/vip-go-mu-plugins/blob/master/wp-cli/vip-migrations.php#L165-L187


## Change Log

[View the change log](https://github.com/Automattic/s3-media-sync/blob/master/CHANGELOG.md).
