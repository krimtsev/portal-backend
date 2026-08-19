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
    public int $timeout = 60;

    public function __construct(
        public readonly string $key,
        public readonly bool   $skipNotifications,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly int    $limit = 1000,
        public readonly int    $offset = 0,
        public readonly bool   $isProtected = false
    ){}

    public function uniqueId(): string
    {
        return "process_mango_call_stats_{$this->key}";
    }

    /**
     * Пауза в 15 секунд перед повтором при ошибке
     */
    public function backoff(): int
    {
        return 15;
    }

    public function handle(MangoCallService $service): void
    {
        try {
            $response = $service->getReportResult($this->key);
            $status = $response['status'] ?? null;

            match ($status) {
                'request', 'work' => $this->handlePendingReport(),
                'complete' => $this->processCompletedReport($service, $response),
                'cancel', 'error', 'not-found' => throw new RuntimeException("Mango API вернул статус ошибки: " . json_encode($response)),
                default => throw new RuntimeException("Неизвестный статус отчета Mango API: " . json_encode($response)),
            };
        } catch (Throwable $exception) {
            Log::channel('mango')
                ->critical('Обработка отчета ProcessMangoCallStats завершилась ошибкой.', [
                    'key'   => $this->key,
                    'error' => $exception->getMessage(),
                ]);

            // Если осталась еще попытка — пробрасываем ошибку для выполнения повтора через 15 сек
            if ($this->attempts() < $this->tries || $this->isProtected) {
                throw $exception;
            }

            $this->delete();
        }
    }

    private function handlePendingReport(): void
    {
        if ($this->attempts() < $this->tries) {
            $this->release(20);
        } else {
            throw new RuntimeException('Превышено количество попыток ожидания отчета Mango (статус request/work).');
        }
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
                isProtected: $this->isProtected
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
