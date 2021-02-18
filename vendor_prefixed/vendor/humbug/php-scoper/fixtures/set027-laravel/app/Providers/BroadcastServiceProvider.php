<?php

namespace WPCOM_VIP\App\Providers;

use WPCOM_VIP\Illuminate\Support\ServiceProvider;
use WPCOM_VIP\Illuminate\Support\Facades\Broadcast;
class BroadcastServiceProvider extends \WPCOM_VIP\Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \WPCOM_VIP\Illuminate\Support\Facades\Broadcast::routes();
        require base_path('routes/channels.php');
    }
}
