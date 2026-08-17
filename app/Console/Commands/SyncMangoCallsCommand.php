<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Mango\RequestMangoCallStatsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class SyncMangoCallsCommand extends Command
{
    protected $signature = 'mango:sync-calls
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--silent : Не отправлять уведомления}';

    protected $description = 'Синхронизация звонков Mango.';

    public function handle(): int
    {
        if (!config('jobs.mango')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $dateOption = $this->option('date');
        $isSilent = (bool) $this->option('silent');

        if ($dateOption) {
            $targetDate = Carbon::parse($dateOption);
            $from = $targetDate->copy()->startOfDay();
            $to = $targetDate->copy()->endOfDay();
            $skipNotifications = true;

            $this->info("Запуск синхронизации за сутки: {$dateOption}. Уведомления отключены.");
        } else {
            $to = Carbon::now();
            $from = $to->copy()->subMinutes(30);
            $skipNotifications = $isSilent;

            $this->info('Запуск синхронизации.');
        }

        RequestMangoCallStatsJob::dispatch($from, $to, $skipNotifications);

        return self::SUCCESS;
    }
}
