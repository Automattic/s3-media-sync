# Changelog for S3 Media Sync

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.1] - 2025-02-14

### Fixed
* Fix bug where media sync hooks are added when s3 bucket settings are empty by @seanlanglands in https://github.com/Automattic/s3-media-sync/pull/34

## [1.4.0] - 2025-01-14

### Added
* Added upload custom WP-CLI command to upload a single attachment by @ovidiul in https://github.com/Automattic/s3-media-sync/pull/20
* Added documentation for WP-CLI commands by @GaryJones in https://github.com/Automattic/s3-media-sync/pull/31

## [1.3.0] - 2025-01-01

### Changed
* Update stop_the_insanity to vip_inmemory_cleanup by @rebeccahum in https://github.com/Automattic/s3-media-sync/pull/16
* update readme to note composer use by @BrookeDot in https://github.com/Automattic/s3-media-sync/pull/21
* Adjustments to terminology and adding some links by @yolih in https://github.com/Automattic/s3-media-sync/pull/22

### Maintenance
* Adding Tests Skeleton by @ovidiul in https://github.com/Automattic/s3-media-sync/pull/25
* Add composer ^2.0 by @matriphe in https://github.com/Automattic/s3-media-sync/pull/24
* Bump aws/aws-sdk-php from 3.150.3 to 3.288.1 by @ovidiul in https://github.com/Automattic/s3-media-sync/pull/26
* chore: Remove composer.lock by @GaryJones in https://github.com/Automattic/s3-media-sync/pull/27
* Define minimum supported PHP version by @GaryJones in https://github.com/Automattic/s3-media-sync/pull/28

## [1.2.0] - 2022-10-27

### Maintenance
* Update dependencies for PHP 8.0 and bump to v1.2.0 by @bratvanov in https://github.com/Automattic/s3-media-sync/pull/18

## [1.1.0] - 2024-04-25

### Fixed
* Check settings exists before hooking for sync by @PatelUtkarsh in https://github.com/Automattic/s3-media-sync/pull/2
* VIP: Eliminate chance of duplicate `wp-content/uploads` in destination path by @jacklenox in https://github.com/Automattic/s3-media-sync/pull/1
* Change hooks to improve reliability by @jacklenox in https://github.com/Automattic/s3-media-sync/pull/5
* Add/upload image edits to s3 by @brettshumaker in https://github.com/Automattic/s3-media-sync/pull/11

### Maintenance
* Update `aws-sdk-php` version by @parkcityj in https://github.com/Automattic/s3-media-sync/pull/3
* Update composer.lock file for aws-sdk-php lib update. by @rahulsprajapati in https://github.com/Automattic/s3-media-sync/pull/4

## 1.0.0 - 2020-06-03
* Initial release.

[1.4.1]: https://github.com/Automattic/s3-media-sync/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/Automattic/s3-media-sync/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/Automattic/s3-media-sync/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/Automattic/s3-media-sync/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/Automattic/s3-media-sync/compare/1.0.0...1.1.0
