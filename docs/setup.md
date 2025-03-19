# Setup Guide

## Build the plugin

This plugin uses [composer](https://getcomposer.org/) as a package manager. After downloading the plugin (as a ZIP file or via `git pull`) run one of the following commands:

* For production: `composer install --no-dev --optimize-autoloader` 
* For development: `composer install` 

Running one of the above commands will create a `vendor` directory which is required for the plugin to function correctly. Applications that are using CI/CD already run one of these commands automatically and can skip this step.

## Activate the plugin

1. [Commit the plugin](https://docs.wpvip.com/technical-references/installing-plugins-best-practices/) to your application's `plugins` directory.
2. Activate the plugin through code or within the WordPress Admin dashboard.
3. [Create an IAM user with Programmatic Access](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_users_create.html).
4. Enter the provided AWS S3 API keys on the plugins's Settings page.
5. Backfill the uploads directory on AWS by running the following command: 

```sh
wp s3-media upload-all --url=example-site.com
```

For more detailed instructions on setting up the AWS permissions, see the [AWS Setup Guide](aws-setup-guide.md). 
