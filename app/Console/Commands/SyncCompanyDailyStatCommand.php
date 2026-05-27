<?php

namespace App\Console\Commands;

use App\Helpers\QueueThrottler;
use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\SyncCompanyDailyStatJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Throwable;

class SyncCompanyDailyStatCommand extends Command
{
    protected $signature = 'yclients:sync-company-stats
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Синхронизация основных показателей компании из YClients';

    public function handle(PeriodResolutionService $periodService): int
    {
        try {
            // Разрешаем период дат через выделенный сервис
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

            foreach (QueueThrottler::chunkWithDelay($partners, 3) as $data) {
                $partner = $data['item'];
                $delay = $data['delay'];

                SyncCompanyDailyStatJob::dispatch(
                    (int) $partner->yclients_id,
                    $dateString
                )->delay($delay);

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены по воркерам.');

        return self::SUCCESS;
    }
}
