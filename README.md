# S3 Media Sync

This plugin syncs the uploads directory on a VIP Go environment to an AWS S3 instance.

Props [S3-Uploads](https://github.com/humanmade/S3-Uploads/) and [Human Made](https://hmn.md/) for creating much of the functionality: https://github.com/humanmade/S3-Uploads

## Setup

* Activate plugin
* Create an IAM user with Programmatic Access
* Enter the provided AWS S3 API keys on the Settings page
* Backfill the uploads directory on AWS by running the following command: 

```
wp s3-media upload-all --url=example-site.com
```

## FAQ

*How can I upload media to a subdirectory in S3?*

Let's say you have a bucket named `my-awesome-site`, but you want all your media to go into the `preprod` subdirectory. On the S3 Media Sync settings page, you would enter the following for the `S3 Bucket Name` field:

```
my-awesome-site/preprod
```

Then, all media will automatically be kept in-sync within `my-awesome-site/preprod/wp-content/uploads`. 

*How can I ensure all the attachments were uploaded?*

You can see which attachments were skipped by running the following command:

```
wp vip migration validate-attachments invalid-attachments.csv --url=example-site.com
```

The log is then available at `invalid-attachments.csv`. The full command can be found here:

https://github.com/Automattic/vip-go-mu-plugins/blob/master/wp-cli/vip-migrations.php#L165-L187
