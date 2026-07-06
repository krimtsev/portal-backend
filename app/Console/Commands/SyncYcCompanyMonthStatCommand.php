<?php

namespace App\Console\Commands;

use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\SyncYcCompanyDailyStatJob;
use App\Jobs\Yclients\SyncYcCompanyMonthStatJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Throwable;

final class SyncYcCompanyMonthStatCommand extends Command
{
    protected $signature = 'yclients:sync-company-month-stats
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Синхронизация основных показателей компании за месяц из YClients';

    public function handle(PeriodResolutionService $periodService): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        try {
            $month = $this->option('month') ?? now()->subMonth()->startOfMonth()->format('Y-m');
            [$startDate, $endDate] = $periodService->resolveMonthBounds($month);

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

        $totalJobs = $partners->count();
        $this->info("Период определен: с {$startDate} по {$endDate}. Активных партнеров: {$totalJobs}");
        $this->info("Стартует отправка {$totalJobs} задач в очередь...");

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($partners as $partner) {
            SyncYcCompanyMonthStatJob::dispatch(
                (int) $partner->yclients_id,
                $startDate,
                $endDate,
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
