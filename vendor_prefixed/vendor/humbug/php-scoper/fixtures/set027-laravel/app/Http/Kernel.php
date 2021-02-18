<?php

namespace WPCOM_VIP\App\Http;

use WPCOM_VIP\Illuminate\Foundation\Http\Kernel as HttpKernel;
class Kernel extends \WPCOM_VIP\Illuminate\Foundation\Http\Kernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [\WPCOM_VIP\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class, \WPCOM_VIP\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, \WPCOM_VIP\App\Http\Middleware\TrimStrings::class, \WPCOM_VIP\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, \WPCOM_VIP\App\Http\Middleware\TrustProxies::class];
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = ['web' => [
        \WPCOM_VIP\App\Http\Middleware\EncryptCookies::class,
        \WPCOM_VIP\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \WPCOM_VIP\Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \WPCOM_VIP\Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \WPCOM_VIP\App\Http\Middleware\VerifyCsrfToken::class,
        \WPCOM_VIP\Illuminate\Routing\Middleware\SubstituteBindings::class,
    ], 'api' => ['throttle:60,1', 'bindings']];
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = ['auth' => \WPCOM_VIP\Illuminate\Auth\Middleware\Authenticate::class, 'auth.basic' => \WPCOM_VIP\Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, 'bindings' => \WPCOM_VIP\Illuminate\Routing\Middleware\SubstituteBindings::class, 'cache.headers' => \WPCOM_VIP\Illuminate\Http\Middleware\SetCacheHeaders::class, 'can' => \WPCOM_VIP\Illuminate\Auth\Middleware\Authorize::class, 'guest' => \WPCOM_VIP\App\Http\Middleware\RedirectIfAuthenticated::class, 'signed' => \WPCOM_VIP\Illuminate\Routing\Middleware\ValidateSignature::class, 'throttle' => \WPCOM_VIP\Illuminate\Routing\Middleware\ThrottleRequests::class];
}
