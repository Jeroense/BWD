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
        $schedule->call('App\Http\Controllers\RobotController@processBolOrders')->everyMinute();
        $schedule->call('App\Http\Controllers\RobotController@requestBolToConstructBolOffersExportCSVFile')
          ->weekdays()
          ->hourly()
          ->timezone('Europe/Amsterdam')
          ->between('8:00', '18:00');
        $schedule->call('App\Services\OfferService@update_process_status_create_offer_export')
        ->weekdays()
        ->hourly()
        ->timezone('Europe/Amsterdam')
        ->between('8:00', '18:00');;


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
