<?php

declare (strict_types=1);
namespace WPCOM_VIP;

require_once __DIR__ . '/../vendor/autoload.php';
use WPCOM_VIP\Set014\Greeter;
echo (new \WPCOM_VIP\Set014\Greeter())->greet() . \PHP_EOL;