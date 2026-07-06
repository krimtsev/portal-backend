<?php

namespace App\Console\Commands;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Records\RecordsResource;
use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\ProcessPartnerStaffDailyStatsJob;
use App\Jobs\Yclients\ProcessPartnerStaffMonthStatsJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncYcStaffMonthStatsCommand extends Command
{
    protected $signature = 'yclients:sync-staff-stats
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients}';

    protected $description = 'Синхронизация статистики по сотрудникам за месяц из YClients';

    /**
     * @throws Throwable
     */
    public function handle(PeriodResolutionService $periodService, RecordsResource $recordsResource): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена.');

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

        $jobs = [];
        foreach ($partners as $partner) {
            $jobs[] = new ProcessPartnerStaffMonthStatsJob(
                (int) $partner->yclients_id,
                $startDate,
                $endDate
            );
        }

        if (empty($jobs)) {
            return self::SUCCESS;
        }

        Bus::batch($jobs)
            ->name("Сбор статистики по сотрудникам за период: {$startDate} - {$endDate}")
            ->onQueue(QueueName::YCLIENTS->value)
            ->allowFailures()
            ->catch(function (Throwable $e) use ($startDate, $endDate) {
                Log::error("Критический сбой пакета статистики сотрудников за период {$startDate} - {$endDate}: {$e->getMessage()}");
            })
            ->finally(function () use ($startDate, $endDate) {
                Log::info("Пакет синхронизации статистики сотрудников за период {$startDate} - {$endDate} завершен.");
            })
            ->dispatch();

        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
