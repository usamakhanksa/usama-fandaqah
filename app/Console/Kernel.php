<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
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
    protected function schedule(Schedule $schedule)
    {
        // auto renew reservations
        $schedule->command('auto-renew:reservation-dateout')->everyMinute()->withoutOverlapping();
        $schedule->command('scth:day-closing-occupancy')->everyMinute()->withoutOverlapping();

        // Daily Brief Report
        $schedule->command('daily:brief-report')->at('06:00');

        // free telescope entries
        $schedule->command('telescope:prune')->daily();

        // private domain create & install & delete
        // $schedule->command('website:create')->everyMinute();
        // $schedule->command('website:install')->everyMinute();
        // $schedule->command('website:delete')->everyMinute();

        // bills account
        // $schedule->command('surebills:create')->everyMinute();

        $schedule->command('expiry:online_reservation')->everyMinute();

        // occupancy ( fandaqah & NTMP (scth) )
        $schedule->command('fandaqah:occupancy-update')->at('07:00')->runInBackground();

        // $schedule->command('prune:activity-logs')->at('04:00');
        // $schedule->command('prune:integration-logs')->at('05:00');

        // $schedule->command('push:shms-failed-jobs')->everyMinute();
        // $schedule->command('supervisor:restart')->cron('0 */6 * * *');

        //Freezing transactions for the business day
        $schedule->command('check:business-day-freeze')->everyMinute()->withoutOverlapping();

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
