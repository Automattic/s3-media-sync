<?php

declare (strict_types=1);
namespace WPCOM_VIP;

require_once __DIR__ . '/../vendor/autoload.php';
use WPCOM_VIP\Set015\Greeter;
$c = new \WPCOM_VIP\Pimple\Container(['hello' => 'Hello world!']);
echo (new \WPCOM_VIP\Set015\Greeter())->greet($c) . \PHP_EOL;
