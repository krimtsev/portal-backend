<?php

declare(strict_types=1);

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\YclientsException;
use App\Jobs\Middleware\ThrottleJobSleep;
use App\Services\Yclients\YcStaffScheduleService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessPartnerStaffMonthStatsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly string $start_date,
        public readonly string $end_date
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    public function middleware(): array
    {
        return [new ThrottleJobSleep()];
    }

    public function uniqueId(): string
    {
        return "yc_partner_staff_month_stats_{$this->companyId}_{$this->start_date}_{$this->end_date}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    /**
     * @throws Throwable
     * @throws YclientsException
     */
    public function handle(YcStaffScheduleService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            /**
             * Собираем уникальные Id сотрудников у которых есть записи оказанных услуг
             */
            $activeStaffIds = $service->getStaffIdsForDate(
                $this->companyId,
                $this->start_date,
                $this->end_date
            );

            if (empty($activeStaffIds)) {
                return;
            }

            $subJobs = array_map(
                fn (int $staffId) => new SyncYcStaffMonthStatsJob(
                    $this->companyId,
                    $staffId,
                    $this->start_date,
                    $this->end_date,
                ),
                $activeStaffIds
            );

            foreach (array_chunk($subJobs, 25) as $chunk) {
                $this->batch()->add($chunk);
            }
        } catch (Throwable $e) {
            Log::error("Ошибка определения списка сотрудников для компании {$this->companyId}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('yclients')
            ->critical('Синхронизация YClients завершилась.', [
                'company_id' => $this->companyId,
                'error'      => $exception->getMessage(),
            ]);
    }
}
