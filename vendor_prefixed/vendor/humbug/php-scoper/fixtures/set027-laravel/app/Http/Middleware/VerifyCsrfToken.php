<?php

namespace WPCOM_VIP\App\Http\Middleware;

use WPCOM_VIP\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
class VerifyCsrfToken extends \WPCOM_VIP\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];
}
