<?php

namespace WPCOM_VIP\App\Console;

use WPCOM_VIP\Illuminate\Console\Scheduling\Schedule;
use WPCOM_VIP\Illuminate\Foundation\Console\Kernel as ConsoleKernel;
class Kernel extends \WPCOM_VIP\Illuminate\Foundation\Console\Kernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(\WPCOM_VIP\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
