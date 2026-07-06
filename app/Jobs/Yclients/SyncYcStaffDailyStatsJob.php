<?php

declare(strict_types=1);

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\YclientsException;
use App\Services\Yclients\SyncYcStaffStatService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncYcStaffDailyStatsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly int $staffId,
        public readonly string $date
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return "yc_staff_daily_stats_{$this->companyId}_{$this->staffId}_{$this->date}";
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
    public function handle(SyncYcStaffStatService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $service->sync($this->companyId, $this->staffId, $this->date);
        } catch (Throwable $e) {
            Log::error("Сбой сбора статистики мастера {$this->staffId} в компании {$this->companyId}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('yclients')
            ->critical('Синхронизация YClients завершилась.', [
                'company_id' => $this->companyId,
                'date'       => $this->date,
                'error'      => $exception->getMessage(),
            ]);
    }
}
