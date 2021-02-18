<?php

namespace WPCOM_VIP;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WPCOM_VIP\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
