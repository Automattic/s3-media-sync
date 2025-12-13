Feature: S3 Media Sync cleanup command
  As a site administrator
  I want to clean up orphaned files from S3
  So that I can remove files no longer referenced by WordPress

  Scenario: Cleanup command shows scanning message
    Given S3 Media Sync is configured
    When I run `wp s3-media cleanup`
    Then STDOUT should contain:
      """
      Scanning S3 for orphaned files
      """

  Scenario: Cleanup command accepts custom path
    Given S3 Media Sync is configured
    When I run `wp s3-media cleanup --path=wp-content/uploads/2024`
    Then STDOUT should contain:
      """
      wp-content/uploads/2024
      """
