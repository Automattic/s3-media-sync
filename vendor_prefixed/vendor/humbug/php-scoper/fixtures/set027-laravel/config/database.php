<?php

namespace WPCOM_VIP;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */
    'default' => \WPCOM_VIP\env('DB_CONNECTION', 'mysql'),
    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */
    'connections' => ['sqlite' => ['driver' => 'sqlite', 'database' => \WPCOM_VIP\env('DB_DATABASE', \WPCOM_VIP\database_path('database.sqlite')), 'prefix' => ''], 'mysql' => ['driver' => 'mysql', 'host' => \WPCOM_VIP\env('DB_HOST', '127.0.0.1'), 'port' => \WPCOM_VIP\env('DB_PORT', '3306'), 'database' => \WPCOM_VIP\env('DB_DATABASE', 'forge'), 'username' => \WPCOM_VIP\env('DB_USERNAME', 'forge'), 'password' => \WPCOM_VIP\env('DB_PASSWORD', ''), 'unix_socket' => \WPCOM_VIP\env('DB_SOCKET', ''), 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => \true, 'engine' => null], 'pgsql' => ['driver' => 'pgsql', 'host' => \WPCOM_VIP\env('DB_HOST', '127.0.0.1'), 'port' => \WPCOM_VIP\env('DB_PORT', '5432'), 'database' => \WPCOM_VIP\env('DB_DATABASE', 'forge'), 'username' => \WPCOM_VIP\env('DB_USERNAME', 'forge'), 'password' => \WPCOM_VIP\env('DB_PASSWORD', ''), 'charset' => 'utf8', 'prefix' => '', 'schema' => 'public', 'sslmode' => 'prefer'], 'sqlsrv' => ['driver' => 'sqlsrv', 'host' => \WPCOM_VIP\env('DB_HOST', 'localhost'), 'port' => \WPCOM_VIP\env('DB_PORT', '1433'), 'database' => \WPCOM_VIP\env('DB_DATABASE', 'forge'), 'username' => \WPCOM_VIP\env('DB_USERNAME', 'forge'), 'password' => \WPCOM_VIP\env('DB_PASSWORD', ''), 'charset' => 'utf8', 'prefix' => '']],
    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */
    'migrations' => 'migrations',
    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */
    'redis' => ['client' => 'predis', 'default' => ['host' => \WPCOM_VIP\env('REDIS_HOST', '127.0.0.1'), 'password' => \WPCOM_VIP\env('REDIS_PASSWORD', null), 'port' => \WPCOM_VIP\env('REDIS_PORT', 6379), 'database' => 0]],
];
