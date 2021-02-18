<?php

declare (strict_types=1);
namespace WPCOM_VIP;

require_once __DIR__ . '/../vendor/autoload.php';
use WPCOM_VIP\Assert\Assertion;
use WPCOM_VIP\Set005\Greeter;
\WPCOM_VIP\Assert\Assertion::true(\true);
echo (new \WPCOM_VIP\Set005\Greeter())->greet() . \PHP_EOL;
