<?php

namespace WPCOM_VIP\App\Http\Middleware;

use WPCOM_VIP\Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
class TrimStrings extends \WPCOM_VIP\Illuminate\Foundation\Http\Middleware\TrimStrings
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = ['password', 'password_confirmation'];
}
