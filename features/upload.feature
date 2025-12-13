Feature: S3 Media Sync upload command
  As a site administrator
  I want to upload media files to S3
  So that I can sync my media library

  Scenario: Upload command requires valid attachment ID
    When I run `wp s3-media upload 0`
    Then the return code should be 1
    And STDERR should contain:
      """
      Invalid attachment ID
      """

  Scenario: Upload command fails for non-existent attachment
    When I run `wp s3-media upload 999999`
    Then the return code should be 1
    And STDERR should contain:
      """
      not found
      """
