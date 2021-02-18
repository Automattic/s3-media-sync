<?php

declare (strict_types=1);
namespace WPCOM_VIP\Acme;

use function call_user_func;
use function file_exists;
use function WPCOM_VIP\iter\toArray;
if (\file_exists($autoload = __DIR__ . '/vendor/scoper-autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}
$range = '\\iter\\range' . '';
\var_dump(\WPCOM_VIP\iter\toArray(\call_user_func($range, 1, 5)));
