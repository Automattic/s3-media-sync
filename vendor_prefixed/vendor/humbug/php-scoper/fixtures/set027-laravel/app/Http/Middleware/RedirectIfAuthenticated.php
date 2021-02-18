<?php

namespace WPCOM_VIP\App\Http\Middleware;

use Closure;
use WPCOM_VIP\Illuminate\Support\Facades\Auth;
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        if (\WPCOM_VIP\Illuminate\Support\Facades\Auth::guard($guard)->check()) {
            return redirect('/home');
        }
        return $next($request);
    }
}
