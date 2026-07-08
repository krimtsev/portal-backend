<?php

declare(strict_types=1);

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Jobs\Middleware\ThrottleJobSleep;
use App\Services\Yclients\SyncYcStaffWorkDaysService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncYcStaffWorkDaysJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    public function middleware(): array
    {
        return [new ThrottleJobSleep()];
    }

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return "yc_staff_work_days_{$this->companyId}_{$this->date}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function handle(SyncYcStaffWorkDaysService $service): void
    {
        $service->sync($this->companyId, $this->date);
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
