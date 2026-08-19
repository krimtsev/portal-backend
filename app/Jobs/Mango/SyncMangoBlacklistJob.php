<?php

declare(strict_types=1);

namespace App\Jobs\Mango;

use App\Services\Mango\MangoBlacklistService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncMangoBlacklistJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 1;

    public function __construct() {}

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return 'mango_blacklist';
    }

    public function handle(MangoBlacklistService $service): void
    {
        try {
            $service->sync();
        } catch (Throwable $exception) {
            Log::channel('mango')
                ->critical('Синхронизация Mango Blacklist завершилась с ошибкой.', [
                    'error' => $exception->getMessage(),
                ]);
        }
    }
}
