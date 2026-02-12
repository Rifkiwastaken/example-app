<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check low stock and send notifications (daily at 8:00 AM)
        $schedule->command('sibesti:check-low-stock')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Jakarta');
        
        // Check expired bin stock and send notifications (daily at 8:00 AM)
        $schedule->command('sibesti:check-expired-bin-stock')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Jakarta');
        
        // Check expiring seeds and send notifications to admin (daily at 8:00 AM)
        $schedule->command('sibesti:check-expiring-seeds')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Jakarta');
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
