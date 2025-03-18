# AWS Setup Guide for S3 Media Sync

This guide explains how to properly set up AWS permissions for the S3 Media Sync plugin. By following these steps, you'll create a secure IAM user with only the permissions needed for the plugin to function.

## Step 1: Create an IAM Group

It's a best practice to create a group with the required permissions, then add users to that group.

1. Log in to the [AWS Management Console](https://console.aws.amazon.com/)
2. Navigate to the IAM service (search for "IAM" in the top search bar)
3. In the left navigation menu, click on "User groups"
4. Click the "Create group" button
5. Enter a name for your group (e.g., "s3-media-sync-users")
6. Skip the "Add users to the group" and "Attach permissions" sections for now
7. Click "Create group"

## Step 2: Create an IAM Policy

Next, create a policy that defines exactly what permissions the plugin needs.

1. In the IAM dashboard, click on "Policies" in the left navigation menu
2. Click "Create policy"
3. Click on the "JSON" tab
4. Delete any existing code in the editor and paste the following:

    ```json
    {
        "Version": "2012-10-17",
        "Statement": [
            {
                "Effect": "Allow",
                "Action": [
                    "s3:ListBucket",
                    "s3:GetBucketLocation"
                ],
                "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME"
            },
            {
                "Effect": "Allow",
                "Action": [
                    "s3:PutObject",
                    "s3:GetObject",
                    "s3:DeleteObject",
                    "s3:PutObjectAcl"
                ],
                "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/wp-content/uploads/*"
            }
        ]
    }
    ```

5. Replace `YOUR-BUCKET-NAME` with the actual name of your S3 bucket (in both places)
6. Click "Next: Tags" (you can skip adding tags)
7. Click "Next: Review"
8. Enter a name for the policy (e.g., "S3-Media-Sync-Policy")
9. Enter a description like "Permissions required for S3 Media Sync WordPress plugin"
10. Click "Create policy"

### Understanding the Required Permissions

The policy above includes the minimum permissions needed for S3 Media Sync to work properly:

- **s3:ListBucket** and **s3:GetBucketLocation**: Allows the plugin to check if the bucket exists and locate it
- **s3:PutObject**: Allows the plugin to upload new media files to S3
- **s3:GetObject**: Allows the plugin to read and serve files from S3
- **s3:DeleteObject**: Allows the plugin to automatically remove files from S3 when they're deleted from the WordPress media library
- **s3:PutObjectAcl**: Allows the plugin to set access controls (public/private) on uploaded files

The permissions are scoped to only apply to the `wp-content/uploads/*` path within your bucket for enhanced security.

## Step 3: Attach the Policy to the Group

1. Go back to "User groups" in the left navigation menu
2. Click on the name of the group you created earlier
3. Go to the "Permissions" tab
4. Click "Add permissions" and select "Attach policies"
5. Search for the policy you just created and select it
6. Click "Add permissions"

## Step 4: Create an IAM User

1. In the IAM dashboard, click on "Users" in the left navigation menu
2. Click "Add users"
3. Enter a username (e.g., "s3-media-sync")
4. Under "Select AWS access type", check "Access key - Programmatic access"
5. Click "Next: Permissions"
6. Choose "Add user to group"
7. Select the group you created earlier
8. Click "Next: Tags" (you can skip adding tags)
9. Click "Next: Review" 
10. Click "Create user"

> **IMPORTANT**: On the success page, you'll see the Access key ID and Secret access key. Copy both of these immediately and store them securely. You will not be able to retrieve the Secret access key again.

## Step 5: Configure S3 Media Sync Plugin

1. In your WordPress admin, go to Settings → S3 Media Sync
2. Enter the following information:
   - **S3 Access Key ID**: The Access key ID from the IAM user you created
   - **S3 Secret Access Key**: The Secret access key from the IAM user you created
   - **S3 Bucket Name**: Your bucket name
   - **S3 Bucket Region**: The AWS region where your bucket is located (e.g., us-east-1, us-west-2)
   - **S3 Object ACL**: Choose "public-read" if you want your media to be publicly accessible, or "private" for restricted access
3. Click "Save Changes"

## Step 6: Test S3 Access

After saving your settings, the plugin will display a "Test S3 Access" button.

1. Click the "Test S3 Access" button
2. If the test succeeds, you'll see a success message
3. If the test fails, you'll see an error message with details about what went wrong

## Troubleshooting Common Issues

### Access Denied Errors

If you get "Access Denied" errors, check:
- The IAM policy is correctly attached to the group
- The IAM user is in the group
- The bucket name is spelled correctly
- The bucket exists in the region you specified
- The bucket policy (if any) doesn't restrict the actions needed by the plugin

### Bucket Does Not Exist Errors

If you get "Bucket does not exist" errors, check:
- The bucket name is spelled correctly (bucket names are case-sensitive)
- The bucket is in the region specified in your settings
- The IAM user has the `s3:ListBucket` and `s3:GetBucketLocation` permissions

### Invalid Credentials Errors

If you get "Invalid credentials" errors, check:
- The Access Key ID and Secret Access Key are entered correctly
- The IAM user is active and not deleted

## Security Best Practices

- Create a dedicated IAM user specifically for this plugin
- Use the principle of least privilege (only grant the permissions needed)
- Regularly rotate your access keys
- Consider using AWS CloudTrail to monitor S3 activity
- If you no longer need the plugin, delete the IAM user to revoke access 
