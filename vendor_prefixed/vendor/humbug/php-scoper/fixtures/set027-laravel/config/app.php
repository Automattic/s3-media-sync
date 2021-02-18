<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'name' => \WPCOM_VIP\env('APP_NAME', 'Laravel'),
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */
    'env' => \WPCOM_VIP\env('APP_ENV', 'production'),
    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */
    'debug' => \WPCOM_VIP\env('APP_DEBUG', \false),
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */
    'url' => \WPCOM_VIP\env('APP_URL', 'http://localhost'),
    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */
    'timezone' => 'UTC',
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */
    'locale' => 'en',
    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */
    'fallback_locale' => 'en',
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */
    'key' => \WPCOM_VIP\env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */
    'providers' => [
        /*
         * Laravel Framework Service Providers...
         */
        \WPCOM_VIP\Illuminate\Auth\AuthServiceProvider::class,
        \WPCOM_VIP\Illuminate\Broadcasting\BroadcastServiceProvider::class,
        \WPCOM_VIP\Illuminate\Bus\BusServiceProvider::class,
        \WPCOM_VIP\Illuminate\Cache\CacheServiceProvider::class,
        \WPCOM_VIP\Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \WPCOM_VIP\Illuminate\Cookie\CookieServiceProvider::class,
        \WPCOM_VIP\Illuminate\Database\DatabaseServiceProvider::class,
        \WPCOM_VIP\Illuminate\Encryption\EncryptionServiceProvider::class,
        \WPCOM_VIP\Illuminate\Filesystem\FilesystemServiceProvider::class,
        \WPCOM_VIP\Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        \WPCOM_VIP\Illuminate\Hashing\HashServiceProvider::class,
        \WPCOM_VIP\Illuminate\Mail\MailServiceProvider::class,
        \WPCOM_VIP\Illuminate\Notifications\NotificationServiceProvider::class,
        \WPCOM_VIP\Illuminate\Pagination\PaginationServiceProvider::class,
        \WPCOM_VIP\Illuminate\Pipeline\PipelineServiceProvider::class,
        \WPCOM_VIP\Illuminate\Queue\QueueServiceProvider::class,
        \WPCOM_VIP\Illuminate\Redis\RedisServiceProvider::class,
        \WPCOM_VIP\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        \WPCOM_VIP\Illuminate\Session\SessionServiceProvider::class,
        \WPCOM_VIP\Illuminate\Translation\TranslationServiceProvider::class,
        \WPCOM_VIP\Illuminate\Validation\ValidationServiceProvider::class,
        \WPCOM_VIP\Illuminate\View\ViewServiceProvider::class,
        /*
         * Package Service Providers...
         */
        /*
         * Application Service Providers...
         */
        \WPCOM_VIP\App\Providers\AppServiceProvider::class,
        \WPCOM_VIP\App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        \WPCOM_VIP\App\Providers\EventServiceProvider::class,
        \WPCOM_VIP\App\Providers\RouteServiceProvider::class,
    ],
];
