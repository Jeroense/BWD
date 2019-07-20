<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Traits\RoundRobin;

class Kernel extends ConsoleKernel
{
    use RoundRobin;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // SmakeRobotController
        $schedule->call('App\Http\Controllers\SmakeRobotController@publishProducts')->everyMinute();
        // $schedule->call('App\Http\Controllers\RobotController@findAndDispatchOrders')->everyMinute();



        // BolRobotController
        $schedule->call('App\Http\Controllers\bolController@processBolOrders')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
