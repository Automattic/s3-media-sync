Feature: S3 Media Sync verify command
  As a site administrator
  I want to verify local media files against S3
  So that I can ensure files are properly synced

  Scenario: Verify command shows warning when no attachments exist
    When I run `wp s3-media verify`
    Then STDOUT should contain:
      """
      No attachments found
      """

  Scenario: Verify command accepts limit parameter
    When I run `wp s3-media verify --limit=5`
    Then STDOUT should contain:
      """
      attachments
      """

  Scenario: Verify command accepts offset parameter
    When I run `wp s3-media verify --offset=0 --limit=1`
    Then STDOUT should contain:
      """
      attachments
      """
