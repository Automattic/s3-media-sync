<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'mailgun' => ['domain' => \WPCOM_VIP\env('MAILGUN_DOMAIN'), 'secret' => \WPCOM_VIP\env('MAILGUN_SECRET')],
    'ses' => ['key' => \WPCOM_VIP\env('SES_KEY'), 'secret' => \WPCOM_VIP\env('SES_SECRET'), 'region' => \WPCOM_VIP\env('SES_REGION', 'us-east-1')],
    'sparkpost' => ['secret' => \WPCOM_VIP\env('SPARKPOST_SECRET')],
    'stripe' => ['model' => \WPCOM_VIP\App\User::class, 'key' => \WPCOM_VIP\env('STRIPE_KEY'), 'secret' => \WPCOM_VIP\env('STRIPE_SECRET')],
];
