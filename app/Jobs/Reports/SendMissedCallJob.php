<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Jobs\Middleware\ThrottleJobSleep;
use App\Services\Reports\MangoCallReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendMissedCallJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly string $entryId,
        public readonly string $calledNumber,
        public readonly string $callerNumber,
        public readonly string $contextStartTime,
        public readonly int $duration
    ) {}

    public function middleware(): array
    {
        return [ThrottleJobSleep::forTelegram()];
    }

    public function uniqueId(): string
    {
        return sprintf(
            'send_missed_call_%s_%s_%s',
            $this->entryId,
            $this->calledNumber,
            $this->callerNumber,
        );
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function handle(MangoCallReportService $service): void
    {
        $service->process(
            $this->calledNumber,
            $this->callerNumber,
            $this->contextStartTime,
            $this->duration
        );
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            'Отправка уведомления о пропущенном звонке в Telegram завершилась ошибкой.',
            [
                'error' => $exception->getMessage(),
            ]
        );
    }
}
