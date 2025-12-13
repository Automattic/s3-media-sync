Feature: S3 Media Sync status command
  As a site administrator
  I want to check the S3 connection status
  So that I can verify my configuration is correct

  Scenario: Status command shows error when not configured
    When I run `wp s3-media status`
    Then the return code should be 1
    And STDERR should contain:
      """
      not properly configured
      """
