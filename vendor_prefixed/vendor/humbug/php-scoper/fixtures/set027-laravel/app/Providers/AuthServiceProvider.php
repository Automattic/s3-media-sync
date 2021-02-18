<?php

namespace WPCOM_VIP\App\Providers;

use WPCOM_VIP\Illuminate\Support\Facades\Gate;
use WPCOM_VIP\Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AuthServiceProvider extends \WPCOM_VIP\Illuminate\Foundation\Support\Providers\AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = ['WPCOM_VIP\\App\\Model' => 'WPCOM_VIP\\App\\Policies\\ModelPolicy'];
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //
    }
}
