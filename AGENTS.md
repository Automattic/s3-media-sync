# S3 Media Sync

Synchronises WordPress media library with Amazon S3.

## Project Knowledge

| Property | Value |
|----------|-------|
| **Main file** | `s3-media-sync.php` |
| **Text domain** | `s3-media-sync` |
| **Namespace** | `wpcomvip\S3MediaSync` (classmap) |
| **Source directory** | `inc/` |
| **Version** | 1.4.1 |
| **Requires PHP** | 8.1+ |

### Directory Structure

```
s3-media-sync/
├── inc/                    # Plugin classes (classmap autoloaded)
│   ├── class-s3-media-sync.php        # Main plugin class
│   ├── class-s3-media-sync-wp-cli.php # WP-CLI commands (excluded from autoload)
│   └── ...
├── tests/
│   └── Integration/        # Integration tests only
├── build/                  # Built assets
├── docs/                   # Documentation
├── languages/              # Translation files
├── .github/workflows/      # CI: integrations
└── .phpcs.xml.dist         # PHPCS configuration
```

### Key Classes

- `S3_Media_Sync` — Main plugin class, handles sync logic
- `S3_Media_Sync_WP_CLI` — WP-CLI commands for bulk sync operations

### Dependencies

- **Runtime**: `aws/aws-sdk-php` (~3.367.2) — Amazon S3 SDK
- **Dev**: `automattic/vipwpcs`, `yoast/wp-test-utils`

## Commands

```bash
composer cs                # Check code standards (PHPCS)
composer cs-fix            # Auto-fix code standard violations
composer lint              # PHP syntax lint
composer test              # Run all tests
composer test:integration  # Run integration tests (requires wp-env)
composer test:integration-ms  # Run multisite integration tests
composer coverage          # Run tests with HTML coverage report
composer i18n              # Generate translation files
```

## Conventions

Follow the standards documented in `~/code/plugin-standards/` for full details. Key points:

- **Commits**: Use the `/commit` skill. Favour explaining "why" over "what".
- **PRs**: Use the `/pr` skill. Squash and merge by default.
- **Branch naming**: `feature/description`, `fix/description` from `develop`.
- **Testing**: This plugin only has integration tests (no unit tests). New tests should be integration tests unless adding pure logic that is independent of WordPress and AWS.
- **Code style**: WordPress coding standards via PHPCS. Tabs for indentation.
- **i18n**: All user-facing strings must use the `s3-media-sync` text domain.

## Architectural Decisions

- **Classmap autoloading**: Uses classmap (not PSR-4) for the `inc/` directory. This is because the class files follow WordPress naming conventions (`class-*.php`), not PSR-4 conventions.
- **WP-CLI excluded from autoload**: The WP-CLI class is explicitly excluded from autoload and loaded conditionally, because WP-CLI may not be available in all environments.
- **AWS SDK as runtime dependency**: The AWS SDK is a Composer runtime dependency, not bundled. The `vendor/` directory must be committed or built during deployment.
- **Integration tests only**: The plugin's logic is tightly coupled to both WordPress and AWS S3. Unit tests with extensive mocking would provide little value, so the test suite focuses on integration tests.

## Common Pitfalls

- Do not edit WordPress core files or bundled dependencies in `vendor/`.
- Run `composer cs` before committing. CI will reject code standard violations.
- Integration tests require `npx wp-env start` running first.
- **AWS credentials**: Tests and local development require valid AWS credentials or mocked S3 endpoints. Do not commit AWS credentials.
- The AWS SDK is large. Be mindful of dependency size when adding features.
- S3 operations can fail due to network issues, permissions, or bucket configuration. Always handle AWS SDK exceptions gracefully — do not let S3 errors crash the WordPress admin.
- File sync operations can be slow for large media libraries. Bulk operations should use WP-CLI, not the web interface.
