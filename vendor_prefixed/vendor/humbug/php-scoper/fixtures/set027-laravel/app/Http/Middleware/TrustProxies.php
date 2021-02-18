<?php

namespace WPCOM_VIP\App\Http\Middleware;

use WPCOM_VIP\Illuminate\Http\Request;
use WPCOM_VIP\Fideloper\Proxy\TrustProxies as Middleware;
class TrustProxies extends \WPCOM_VIP\Fideloper\Proxy\TrustProxies
{
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = \WPCOM_VIP\Illuminate\Http\Request::HEADER_X_FORWARDED_ALL;
}
