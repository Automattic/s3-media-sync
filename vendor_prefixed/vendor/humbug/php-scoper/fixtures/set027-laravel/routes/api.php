<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Illuminate\Http\Request;
use WPCOM_VIP\Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
\WPCOM_VIP\Illuminate\Support\Facades\Route::middleware('auth:api')->get('/user', function (\WPCOM_VIP\Illuminate\Http\Request $request) {
    return $request->user();
});
