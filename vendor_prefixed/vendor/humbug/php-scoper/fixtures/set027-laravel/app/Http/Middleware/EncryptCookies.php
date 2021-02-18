<?php

namespace WPCOM_VIP\App\Http\Middleware;

use WPCOM_VIP\Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
class EncryptCookies extends \WPCOM_VIP\Illuminate\Cookie\Middleware\EncryptCookies
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];
}
