<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */
    'default' => \WPCOM_VIP\env('FILESYSTEM_DRIVER', 'local'),
    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */
    'cloud' => \WPCOM_VIP\env('FILESYSTEM_CLOUD', 's3'),
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */
    'disks' => ['local' => ['driver' => 'local', 'root' => \WPCOM_VIP\storage_path('app')], 'public' => ['driver' => 'local', 'root' => \WPCOM_VIP\storage_path('app/public'), 'url' => \WPCOM_VIP\env('APP_URL') . '/storage', 'visibility' => 'public'], 's3' => ['driver' => 's3', 'key' => \WPCOM_VIP\env('AWS_ACCESS_KEY_ID'), 'secret' => \WPCOM_VIP\env('AWS_SECRET_ACCESS_KEY'), 'region' => \WPCOM_VIP\env('AWS_DEFAULT_REGION'), 'bucket' => \WPCOM_VIP\env('AWS_BUCKET'), 'url' => \WPCOM_VIP\env('AWS_URL')]],
];
