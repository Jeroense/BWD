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
        // $schedule->call('App\Http\Controllers\RobotController@processBolOrders')->everyThirtyMinutes();

        $schedule->call('App\Http\Controllers\RobotController@requestBolToConstructBolOffersExportCSVFile')
          ->weekdays()
          ->dailyAt('12:02')       // heeft geen zin om dit elk half uur te doen, je krijgt een kopie van de vorige export dan, zie uitleg in GetBolProcessStatusJob.php
          ->timezone('Europe/Amsterdam');
        //   ->between('8:00', '18:00');

        $schedule->call('App\Http\Controllers\RobotController@update_offer_export_process_statusses_in_local_DB')
        ->weekdays()
        ->everyTenMinutes()
        ->timezone('Europe/Amsterdam');
        // ->between('8:15', '18:15');

        $schedule->call('App\Http\Controllers\RobotController@updatePendingProcessStatusses')
        // ->weekdays()
        // ->everyFiveMinutes()
        ->everyMinute()
        ->timezone('Europe/Amsterdam');
        // ->between('8:15', '18:15');

        // $schedule->call('App\Http\Controllers\RobotController@processBolOrders')
        // ->everyFiveMinutes()
        // ->timezone('Europe/Amsterdam');

        // $schedule->call('App\Http\Controllers\RobotController@publishProducts')->everyMinute();
        // $schedule->call('App\Http\Controllers\RobotController@statusCheck')->everyMinute();
        // $schedule->call('App\Http\Controllers\RobotController@changeName')->everyMinute();
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
