<?php

declare (strict_types=1);
namespace WPCOM_VIP;

use WPCOM_VIP\Acme\Foo as FooClass;
use const WPCOM_VIP\Acme\FOO as FOO_CONST;
use function WPCOM_VIP\Acme\foo as foo_func;
if (\file_exists($autoload = __DIR__ . '/vendor/scoper-autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}
(new \WPCOM_VIP\Acme\Foo())();
\WPCOM_VIP\Acme\foo();
echo \WPCOM_VIP\Acme\FOO . \PHP_EOL;
