<?php

declare (strict_types=1);
namespace WPCOM_VIP;

$autoload = __DIR__ . '/vendor/scoper-autoload.php';
if (\false === \file_exists($autoload)) {
    $autoload = __DIR__ . '/vendor/autoload.php';
}
require_once $autoload;
echo \WPCOM_VIP\foo() ? 'ok' : 'ko';
echo \PHP_EOL;
echo \WPCOM_VIP\bar() ? 'ok' : 'ko';
echo \PHP_EOL;
