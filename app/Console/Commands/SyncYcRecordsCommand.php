<?php

namespace App\Console\Commands;

use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\SyncYcRecordsJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Throwable;

final class SyncYcRecordsCommand extends Command
{
    protected $signature = 'yclients:sync-records
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Синхронизация списка записей компании из YClients';

    public function handle(PeriodResolutionService $periodService): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        try {
            $dates = $periodService->resolveFromParams(
                date: $this->option('date'),
                month: $this->option('month')
            );
        } catch (Throwable $e) {
            $this->error('Ошибка параметров: ' . $e->getMessage());

            return self::FAILURE;
        }

        $query = Partner::query()->withActiveYclients();

        if ($companyId = $this->option('company_id')) {
            $query->where('yclients_id', $companyId);
        }

        $partners = $query->get(['id', 'yclients_id']);

        if ($partners->isEmpty()) {
            $this->warn('Нет активных партнеров с привязанным yclients_id для обработки.');

            return self::SUCCESS;
        }

        $totalJobs = $partners->count() * count($dates);
        $this->info('Период определен. Дней: ' . count($dates) . '. Активных партнеров: ' . $partners->count());
        $this->info("Стартует отправка {$totalJobs} задач в очередь...");

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($dates as $date) {
            $dateString = $date->toDateString();

            foreach ($partners as $partner) {
                SyncYcRecordsJob::dispatch(
                    (int) $partner->yclients_id,
                    $dateString
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
