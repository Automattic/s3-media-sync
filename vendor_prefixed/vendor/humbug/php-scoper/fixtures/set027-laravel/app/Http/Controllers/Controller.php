<?php

namespace WPCOM_VIP\App\Http\Controllers;

use WPCOM_VIP\Illuminate\Foundation\Bus\DispatchesJobs;
use WPCOM_VIP\Illuminate\Routing\Controller as BaseController;
use WPCOM_VIP\Illuminate\Foundation\Validation\ValidatesRequests;
use WPCOM_VIP\Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class Controller extends \WPCOM_VIP\Illuminate\Routing\Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
