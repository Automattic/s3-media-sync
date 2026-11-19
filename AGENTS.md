# S3 Media Sync

Synchronizes the WordPress media library with Amazon S3.

## Project Knowledge

| Property | Value |
|----------|-------|
| **Main file** | `s3-media-sync.php` |
| **Text domain** | `s3-media-sync` |
| **Namespace** | Top-level classes in `inc/` are global, apart from `S3_Media_Sync\S3_Media_Sync_Client_Factory`. The subdirectories use `S3_Media_Sync\Exceptions`, `S3_Media_Sync\Services` and `S3_Media_Sync\Value_Objects`. All are classmap autoloaded. |
| **Source directory** | `inc/` |
| **Version** | 1.4.1 |
| **Requires PHP** | 8.1+ |

### Directory Structure

```
s3-media-sync/
├── inc/                    # Plugin classes (classmap autoloaded)
│   ├── class-s3-media-sync.php        # Main plugin class
│   ├── class-s3-media-sync-wp-cli.php # WP-CLI commands (excluded from autoload)
│   ├── exceptions/         # Invalid bucket, file and region exceptions
│   ├── services/           # S3 repository
│   ├── value-objects/      # S3 bucket, S3 file, local file, region and so on
│   └── ...
├── tests/
│   └── Integration/        # Integration tests only
├── docs/                   # Documentation
├── languages/              # POT file
├── .github/workflows/      # CI: integration tests and release
└── .phpcs.xml.dist         # PHPCS configuration
```

### Key Classes

- `S3_Media_Sync` — Main plugin class, handles sync logic
- `S3_Media_Sync_WP_CLI_Command` — WP-CLI commands for bulk sync operations

### Dependencies

- **Runtime**: `aws/aws-sdk-php` — Amazon S3 SDK
- **Dev**: `automattic/vipwpcs`, `yoast/wp-test-utils`

## Commands

```bash
composer cs                # Check code standards (PHPCS)
composer cs-fix            # Auto-fix code standard violations
composer lint              # PHP syntax lint
composer test              # Run the integration tests (single site)
composer test:integration  # Run integration tests (requires wp-env)
composer test:integration-ms  # Run multisite integration tests
composer coverage          # Run tests with a coverage report in build/coverage
composer i18n              # Regenerate the POT file
```

## Conventions

See `CONTRIBUTING.md` for setup, checks and the contribution workflow. Key points:

- **Commits**: Every commit must be signed; `develop` and `main` reject unsigned commits. Use a [Conventional Commits](https://www.conventionalcommits.org/) prefix (`fix:`, `feat:`, `test:`, `docs:`, `ci:`, `chore:` and so on) with an imperative subject of about 50 characters, and a body that explains why the change was needed rather than what changed. One concern per commit.
- **PRs**: Open against `develop`. The title describes the outcome in sentence case, without a conventional commit prefix. The description explains the problem first, then how the PR solves it. PRs land as merge commits (squash and rebase merging are disabled), so every commit message reaches `develop` as written.
- **Testing**: This plugin only has integration tests (no unit tests). New tests should be integration tests unless adding pure logic that is independent of WordPress and AWS.
- **Code style**: WordPress coding standards via PHPCS. Tabs for indentation.
- **i18n**: All user-facing strings must use the `s3-media-sync` text domain.

## Architectural Decisions

- **Classmap autoloading**: Uses classmap (not PSR-4) for the `inc/` directory. This is because the class files follow WordPress naming conventions (`class-*.php`), not PSR-4 conventions.
- **WP-CLI excluded from autoload**: The WP-CLI class is explicitly excluded from autoload and loaded conditionally, because WP-CLI may not be available in all environments.
- **AWS SDK as runtime dependency**: The AWS SDK is a Composer runtime dependency. `vendor/` isn't committed: the release workflow runs `composer install --no-dev` and ships it in the release ZIP, so a deployment from source has to run Composer.
- **Integration tests only**: The plugin's logic is tightly coupled to both WordPress and AWS S3. Unit tests with extensive mocking would provide little value, so the test suite focuses on integration tests.

## Common Pitfalls

- Do not edit WordPress core files or bundled dependencies in `vendor/`.
- Run `composer cs` on the files you change. CI doesn't run PHPCS, and older files have many existing violations, so fix only what your change touches.
- Integration tests require `npx wp-env start` running first.
- **AWS credentials**: The integration tests mock the S3 client, so they don't need credentials. Trying the plugin against a real bucket does. Never commit AWS credentials.
- The AWS SDK is large. Be mindful of dependency size when adding features.
- S3 operations can fail due to network issues, permissions, or bucket configuration. Always handle AWS SDK exceptions gracefully — do not let S3 errors crash the WordPress admin.
- File sync operations can be slow for large media libraries. Bulk operations should use WP-CLI, not the web interface.
