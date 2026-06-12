<?php

namespace App\Console\Commands;

use App\Integrations\Yclients\Resources\Records\RecordsResource; // Предполагаемый ресурс записей
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\Services\PeriodResolutionService;
use App\Jobs\Yclients\SyncStaffDailyStatJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Throwable;

class SyncYcStaffDailyStatsCommand extends Command
{
    protected $signature = 'yclients:sync-staff-stats
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--company_id= : Конкретный ID компании из YClients}';

    protected $description = 'Парсинг работавших сотрудников и сбор их дневной статистики';

    public function handle(PeriodResolutionService $periodService, RecordsResource $recordsResource): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена.');

            return self::SUCCESS;
        }

        try {
            $dates = $periodService->resolveFromParams(
                date: $this->option('date')
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

        foreach ($dates as $date) {
            $dateString = $date->toDateString();

            foreach ($partners as $partner) {
                try {
                    // 1. Получаем записи напрямую из API за этот день
                    // (Или используем локальный репозиторий, если уверены в нем, но API надежнее)
                    $rawRecords = $recordsResource->getRecords($partner->yclients_id, ['date' => $dateString]);

                    // Предполагаем, что получаем массив или коллекцию объектов RecordsResponse
                    $records = collect($rawRecords)->map(fn($r) => RecordsResponse::fromArray($r));

                    // 2. Фильтруем записи и получаем уникальные ID сотрудников
                    $activeStaffIds = $records
                        ->filter(function (RecordsResponse $record) {
                            // Проверяем наличие сотрудника и что у записи есть услуги (массив services не пустой)
                            return $record->staff_id > 0 && !empty($record->services);
                        })
                        ->pluck('staff_id')
                        ->unique();

                    if ($activeStaffIds->isEmpty()) {
                        $this->info("Нет активных мастеров с услугами для компании {$partner->yclients_id} на дату {$dateString}");
                        continue;
                    }

                    // 3. Распределяем задачи по каждому сотруднику в очередь
                    foreach ($activeStaffIds as $staffId) {
                        SyncStaffDailyStatJob::dispatch(
                            (int) $partner->yclients_id,
                            (int) $staffId,
                            $dateString
                        );
                    }

                } catch (Throwable $e) {
                    $this->error("Ошибка обработки компании {$partner->yclients_id}: " . $e->getMessage());
                    // Продолжаем обработку остальных партнеров, не падаем целиком
                    continue;
                }
            }
        }

        $this->info('Все задачи для сотрудников успешно отправлены в очередь.');
        return self::SUCCESS;
    }
}
