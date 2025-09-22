<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Запуск вашей команды каждый день в 03:00
        $schedule->command('certificates:update')->dailyAt('03:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // Для closure-команд (если нужно)
        // require base_path('routes/console.php');
    }
}
