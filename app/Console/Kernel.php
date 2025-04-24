<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $notificationTime = env('NOTIFICATION_TIME', 'everyMinute'); // Default to every minute if not set

    // Dynamic scheduling based on the value of NOTIFICATION_TIME in the .env file
    switch ($notificationTime) {
        case 'everyMinute':
            $schedule->command('send:mq2Notify')->everyMinute();
            $schedule->command('send:humidNotify')->everyMinute();
            $schedule->command('send:soilNotify')->everyMinute();
            $schedule->command('send:temNotify')->everyMinute();
            break;

        case 'everyFiveMinutes':
            $schedule->command('send:mq2Notify')->everyFiveMinutes();
            $schedule->command('send:humidNotify')->everyFiveMinutes();
            $schedule->command('send:soilNotify')->everyFiveMinutes();
            $schedule->command('send:temNotify')->everyFiveMinutes();
            break;

        case 'hourly':
            $schedule->command('send:mq2Notify')->hourly();
            $schedule->command('send:humidNotify')->hourly();
            $schedule->command('send:soilNotify')->hourly();
            $schedule->command('send:temNotify')->hourly();
            break;

        // You can add other cases here as needed...
    }
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
