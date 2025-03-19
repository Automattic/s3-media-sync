# S3 Media Sync

A WordPress plugin that syncs media uploads to Amazon S3, providing reliable cloud storage for your WordPress media library.

## Overview

S3 Media Sync syncs the `uploads` directory of a WordPress environment to an AWS S3 instance, ensuring your media files are safely stored and easily accessible. This plugin is ideal for WordPress sites that need:

- Cloud-based media storage
- Better reliability and scalability for media files
- Improved performance for media-heavy sites

*Props to [S3-Uploads](https://github.com/humanmade/S3-Uploads/) and [Human Made](https://hmn.md/) for creating much of the functionality on which this plugin is based.*

## Key Features

- **Automatic Syncing**: Automatically uploads media files to S3 as they're added to WordPress
- **WP-CLI Integration**: Command-line tools for bulk operations and management
- **Configurable Storage**: Support for custom bucket paths and AWS regions
- **Simple Setup**: Easy-to-use settings page for configuration

## Documentation

For detailed information, please see the documentation in the `docs` directory:

- [Setup Guide](docs/setup.md) - How to install and configure the plugin
- [AWS Setup Guide](docs/aws-setup-guide.md) - Instructions for setting up AWS permissions
- [WP-CLI Commands](docs/wp-cli-commands.md) - Available command-line tools
- [Development Guide](docs/development.md) - Information for developers
- [FAQ](docs/faq.md) - Frequently asked questions

## Quick Start

1. Install and activate the plugin
2. Configure AWS credentials in the settings page
3. Start uploading media to WordPress - it will automatically sync to S3

For full installation instructions, see the [Setup Guide](docs/setup.md).

## Change Log

[View the change log](https://github.com/Automattic/s3-media-sync/blob/master/CHANGELOG.md).

## Support

For issues and feature requests, please [create an issue](https://github.com/Automattic/s3-media-sync/issues) on the GitHub repository.
