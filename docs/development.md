# Development Guide

## Development Setup

### Git Hooks

To ensure code quality, you can install the provided pre-commit hook:

```sh
# Install the pre-commit hook
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

This hook will automatically run code quality checks before each commit.

## Integration Tests

Start your local development environment of choice and run the `bin/install-wp-tests.sh` script to set up the
database and install a copy of WordPress in your computer's `/tmp` directory.

### Test Setup

```bash
bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
```

#### For VVV

```bash
bash bin/install-wp-tests.sh s3_media_sync_test root root localhost latest
```

#### For Local

```bash
bash bin/install-wp-tests.sh s3_media_sync_test root root localhost:"<path_to_sock>" latest
```

#### For VIP Local Development Environment

```bash
bash bin/install-wp-tests.sh s3_media_sync_test root "" 127.0.0.1:"<port>" latest 
```

#### Troubleshooting

Try deleting your tmp `/wordpress/` and `/wordpress-tests-lib/` folders if you're seeing missing file errors related to
these directories.

### Running Tests

To run all tests:

```sh
composer test
```

To run only unit tests:

```sh
composer test-unit
```

To run only integration tests:

```sh
composer test-integration
```

## Coding Standards

This plugin follows the WordPress coding standards. To check your code for standards compliance, run:

```sh
composer phpcs
```

To automatically fix many common coding standards issues:

```sh
composer phpcbf
```

To run static analysis with PHPStan:

```sh
composer phpstan
```

To run all linting tools:

```sh
composer lint
```

To automatically fix coding standards issues:

```sh
composer lint-fix
``` 
