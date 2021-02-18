<?php

namespace WPCOM_VIP;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use WPCOM_VIP\Illuminate\Support\Facades\Route;
\WPCOM_VIP\Illuminate\Support\Facades\Route::get('/', function () {
    return \WPCOM_VIP\view('welcome');
});
