<?php

declare(strict_types=1);

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\YclientsException;
use App\Services\Yclients\YcStaffScheduleService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessPartnerStaffDailyStatsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly string $date
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    public function uniqueId(): string
    {
        return "yc_partner_staff_stats_{$this->companyId}_{$this->date}";
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
            $activeStaffIds = $service->getStaffIdsForDate($this->companyId, $this->date);

            /**
             * Собираем уникальные Id сотрудников у которых есть записи оказанных услуг
             */
            if (empty($activeStaffIds)) {
                return;
            }

            $subJobs = array_map(
                fn (int $staffId) => new SyncYcStaffDailyStatsJob($this->companyId, $staffId, $this->date),
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
