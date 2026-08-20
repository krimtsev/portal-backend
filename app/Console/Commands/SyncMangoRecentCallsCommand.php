<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Mango\RequestMangoCallStatsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class SyncMangoRecentCallsCommand extends Command
{
    private const TIMEZONE = 'Europe/Moscow';

    protected $signature = 'mango:sync-recent-calls
                            {--silent : Не отправлять уведомления}';

    protected $description = 'Синхронизация текущих пропущенных звонков Mango (за последние 30 минут).';

    public function handle(): int
    {
        if (!config('jobs.mango')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $skipNotifications = (bool) $this->option('silent');

        $to = Carbon::now(self::TIMEZONE);
        $from = $to->copy()->subMinutes(30);

        RequestMangoCallStatsJob::dispatch(
            $from,
            $to,
            $skipNotifications,
            1000,
            0,
            false
        );

        $this->info('Запуск синхронизации текущих звонков завершен.');

        return self::SUCCESS;
    }
}
