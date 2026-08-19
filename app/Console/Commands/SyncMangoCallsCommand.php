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
                            {--silent : Не отправлять уведомления}
                            {--protected : Не удалять задачи в случае неудачи}';

    protected $description = 'Синхронизация звонков Mango.';

    public function handle(): int
    {
        if (!config('jobs.mango')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $dateOption = $this->option('date');
        $skipNotifications = (bool) $this->option('silent');
        $isProtected = (bool) $this->option('protected');

        $limit = 1000;

        if ($dateOption) {
            $targetDate = Carbon::parse($dateOption);
            $from = $targetDate->copy()->startOfDay();
            $to = $targetDate->copy()->endOfDay();
            $limit = 5000;
            $skipNotifications = true;
            $isProtected = true;
            $this->info("Запуск синхронизации за сутки: {$dateOption}. Уведомления отключены.");
        } else {
            $to = Carbon::now();
            $from = $to->copy()->subMinutes(30);
            $this->info('Запуск синхронизации.');
        }

        RequestMangoCallStatsJob::dispatch(
            $from,
            $to,
            $skipNotifications,
            $limit,
            0,
            $isProtected
        );

        return self::SUCCESS;
    }
}
