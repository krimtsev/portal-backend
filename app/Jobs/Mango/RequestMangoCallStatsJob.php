<?php

declare(strict_types=1);

namespace App\Jobs\Mango;

use App\Services\Mango\MangoCallService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RequestMangoCallStatsJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения */
    public int $tries = 1;

    public function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly bool $skipNotifications = false,
        public readonly int $limit = 1000,
        public readonly int $offset = 0,
    ) {}

    public function uniqueId(): string
    {
        return sprintf(
            'request_mango_call_stats_%d_%d_%d_%d',
            $this->from->timestamp,
            $this->to->timestamp,
            $this->limit,
            $this->offset,
        );
    }

    public function handle(MangoCallService $service): void
    {
        $key = $service->getStatsKey(
            from: $this->from,
            to: $this->to,
            limit: $this->limit,
            offset: $this->offset,
        );

        if ($key) {
            $callStatsDelay = config('mango.call_stats_delay');

            ProcessMangoCallStatsJob::dispatch(
                key: $key,
                skipNotifications: $this->skipNotifications,
                from: $this->from,
                to: $this->to,
                limit: $this->limit,
                offset: $this->offset,
            )->delay(now()->addSeconds($callStatsDelay));
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('mango')
            ->critical('Запрос формирования отчета Mango завершился ошибкой.', [
                'error'  => $exception->getMessage(),
                'offset' => $this->offset,
                'limit'  => $this->limit,
            ]);
    }
}
