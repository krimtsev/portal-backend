<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Mango\RequestMangoCallStatsJob;
use Illuminate\Console\Command;

final class SyncMangoDailyCallsCommand extends Command
{
    protected $signature = 'mango:sync-daily-calls
                            {--date= : Конкретный день в формате YYYY-MM-DD}';

    protected $description = 'Синхронизация звонков Mango за суточный период (по умолчанию — вчера).';

    public function handle(PeriodResolutionService $periodResolutionService): int
    {
        if (!config('jobs.mango')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $dateOption = $this->option('date');

        try {
            $dates = $periodResolutionService->resolveFromParams(date: $dateOption);
            $targetDate = $dates[0];
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $from = $targetDate->copy()->startOfDay();
        $to = $targetDate->copy()->endOfDay();

        RequestMangoCallStatsJob::dispatch(
            $from,
            $to,
            true,
            5000,
            0,
            true
        );

        $this->info("Запуск суточной синхронизации за {$targetDate->toDateString()} завершен.");

        return self::SUCCESS;
    }
}
