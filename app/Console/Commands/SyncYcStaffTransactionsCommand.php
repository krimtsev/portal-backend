<?php

namespace App\Console\Commands;

use App\Enums\QueueName;
use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\ProcessPartnerStaffTransactionJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncYcStaffTransactionsCommand extends Command
{
    protected $signature = 'yclients:sync-staff-transactions
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Синхронизация транзакций компании из YClients по сотрудникам';

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

            $jobs = [];
            foreach ($partners as $partner) {
                $jobs[] = new ProcessPartnerStaffTransactionJob(
                    (int) $partner->yclients_id,
                    $dateString
                );
            }

            if (empty($jobs)) {
                continue;
            }

            Bus::batch($jobs)
                ->name("Сбор транзакций по сотрудникам за: {$date}")
                ->onQueue(QueueName::YCLIENTS->value)
                ->allowFailures()
                ->catch(function (Throwable $e) use ($date) {
                    Log::error("Критический сбой пакета транзакций сотрудников за {$date}: {$e->getMessage()}");
                })
                ->finally(function () use ($date) {
                    Log::info("Пакет синхронизации транзакций сотрудников за {$date} завершен.");
                })
                ->dispatch();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
