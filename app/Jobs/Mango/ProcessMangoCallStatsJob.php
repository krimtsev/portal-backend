<?php

declare(strict_types=1);

namespace App\Jobs\Mango;

use App\Services\Mango\MangoCallService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class ProcessMangoCallStatsJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения */
    public int $tries = 2;

    /** Таймаут выполнения */
    public int $timeout = 120;

    public function __construct(
        public readonly string $key,
        public readonly bool   $skipNotifications,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly int    $limit = 1000,
        public readonly int    $offset = 0,
    ){}

    public function uniqueId(): string
    {
        return "process_mango_call_stats_{$this->key}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     */
    public function backoff(): array
    {
        return [120, 240];
    }

    public function handle(MangoCallService $service): void
    {
        $response = $service->getReportResult($this->key);
        $status = $response['status'] ?? null;

        match ($status) {
            'request', 'work' => $this->release(15),
            'complete' => $this->processCompletedReport($service, $response),
            'cancel', 'error', 'not-found' => throw new RuntimeException("Mango API вернул статус ошибки: {$status}"),
            default => throw new RuntimeException("Неизвестный статус отчета Mango API: " . json_encode($status)),
        };
    }

    private function processCompletedReport(MangoCallService $service, array $response): void
    {
        $dataGroup = $response['data'][0] ?? [];
        $callsData = $dataGroup['list'] ?? [];

        if (!empty($callsData)) {
            $service->process($callsData, $this->skipNotifications);
        }

        if (count($callsData) === $this->limit) {
            RequestMangoCallStatsJob::dispatch(
                from: $this->from,
                to: $this->to,
                skipNotifications: $this->skipNotifications,
                limit: $this->limit,
                offset: $this->offset + $this->limit,
            );
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('mango')
            ->critical('Обработка отчета ProcessMangoCallStats завершилась ошибкой.', [
                'key' => $this->key,
                'error' => $exception->getMessage(),
            ]);
    }
}
