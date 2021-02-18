<?php

namespace WPCOM_VIP\App\Providers;

use WPCOM_VIP\Illuminate\Support\Facades\Event;
use WPCOM_VIP\Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
class EventServiceProvider extends \WPCOM_VIP\Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = ['WPCOM_VIP\\App\\Events\\Event' => ['WPCOM_VIP\\App\\Listeners\\EventListener']];
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }
}
