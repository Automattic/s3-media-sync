<?php

namespace WPCOM_VIP;

use WPCOM_VIP\App\Kernel;
use WPCOM_VIP\Symfony\Component\Debug\Debug;
use WPCOM_VIP\Symfony\Component\Dotenv\Dotenv;
use WPCOM_VIP\Symfony\Component\HttpFoundation\Request;
require __DIR__ . '/../vendor/autoload.php';
// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    if (!\class_exists(\WPCOM_VIP\Symfony\Component\Dotenv\Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new \WPCOM_VIP\Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/../.env');
}
$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $env);
if ($debug) {
    \umask(00);
    \WPCOM_VIP\Symfony\Component\Debug\Debug::enable();
}
if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? \false) {
    \WPCOM_VIP\Symfony\Component\HttpFoundation\Request::setTrustedProxies(\explode(',', $trustedProxies), \WPCOM_VIP\Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_ALL ^ \WPCOM_VIP\Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST);
}
if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? \false) {
    \WPCOM_VIP\Symfony\Component\HttpFoundation\Request::setTrustedHosts(\explode(',', $trustedHosts));
}
$kernel = new \WPCOM_VIP\App\Kernel($env, $debug);
$request = \WPCOM_VIP\Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
