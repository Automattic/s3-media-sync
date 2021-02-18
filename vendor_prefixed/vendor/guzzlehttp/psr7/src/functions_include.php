<?php

namespace WPCOM_VIP;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WPCOM_VIP\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
