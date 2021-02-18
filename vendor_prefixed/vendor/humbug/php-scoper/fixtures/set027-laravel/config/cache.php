<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    | Supported: "apc", "array", "database", "file", "memcached", "redis"
    |
    */
    'default' => \WPCOM_VIP\env('CACHE_DRIVER', 'file'),
    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */
    'stores' => ['apc' => ['driver' => 'apc'], 'array' => ['driver' => 'array'], 'database' => ['driver' => 'database', 'table' => 'cache', 'connection' => null], 'file' => ['driver' => 'file', 'path' => \WPCOM_VIP\storage_path('framework/cache/data')], 'memcached' => ['driver' => 'memcached', 'persistent_id' => \WPCOM_VIP\env('MEMCACHED_PERSISTENT_ID'), 'sasl' => [\WPCOM_VIP\env('MEMCACHED_USERNAME'), \WPCOM_VIP\env('MEMCACHED_PASSWORD')], 'options' => [], 'servers' => [['host' => \WPCOM_VIP\env('MEMCACHED_HOST', '127.0.0.1'), 'port' => \WPCOM_VIP\env('MEMCACHED_PORT', 11211), 'weight' => 100]]], 'redis' => ['driver' => 'redis', 'connection' => 'default']],
    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */
    'prefix' => \WPCOM_VIP\env('CACHE_PREFIX', \WPCOM_VIP\str_slug(\WPCOM_VIP\env('APP_NAME', 'laravel'), '_') . '_cache'),
];
