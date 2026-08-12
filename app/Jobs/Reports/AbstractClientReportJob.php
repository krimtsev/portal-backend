<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Enums\Report\ClientReportType;
use App\Jobs\Middleware\ThrottleJobSleep;
use App\Models\Partner\Partner;
use App\Services\Reports\ClientReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractClientReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public Partner $partner
    ) {}

    abstract protected function reportType(): ClientReportType;

    public function middleware(): array
    {
        return [ThrottleJobSleep::forTelegram()];
    }

    /**
     * Уникальный ID задачи.
     */
    public function uniqueId(): string
    {
        return sprintf(
            'client_report_%s_%s_%s',
            $this->partner->id,
            $this->reportType()->value,
            now()->format('Y_m_d')
        );
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function handle(ClientReportService $service): void
    {
        $service->process($this->partner, $this->reportType());
    }

    /**
     * Логирование при окончательном падении задачи.
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            "Отправка отчета ({$this->reportType()->label()}) в Telegram завершилась ошибкой.",
            [
                'partner_id'  => $this->partner->id,
                'yclients_id' => $this->partner->yclients_id,
                'error'       => $exception->getMessage(),
            ]
        );
    }
}
