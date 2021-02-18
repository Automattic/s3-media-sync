<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Illuminate\Foundation\Inspiring;
use WPCOM_VIP\Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/
\WPCOM_VIP\Illuminate\Support\Facades\Artisan::command('inspire', function () {
    $this->comment(\WPCOM_VIP\Illuminate\Foundation\Inspiring::quote());
})->describe('Display an inspiring quote');
