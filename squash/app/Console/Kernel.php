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
    protected $commands = [
        Commands\DailyReports::class,
        Commands\SendReport::class,
        Commands\ImportEmoji::class,
        Commands\ImportDone::class,
        Commands\CleanEntries::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      // Every hour, send out any daily reports that are scheduled for that hour
      $schedule->command('report:daily')->hourly();
    }
}
