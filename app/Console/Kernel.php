<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The application's command schedule.
     *
     * @var array
     */
    protected $commands = [
        Commands\CompressProjectFolders::class,
        Commands\MakeFullResource::class,
        Commands\UpdateModelFromMigration::class,
        Commands\ConvertMsgJson::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('queue:work')->everyMinute();
        // Prune Telescope entries every minute
        $schedule->command('telescope:prune')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
