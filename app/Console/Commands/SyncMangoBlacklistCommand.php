<?php

namespace App\Console\Commands;

use App\Jobs\Mango\SyncMangoBlacklistJob;
use Illuminate\Console\Command;

final class SyncMangoBlacklistCommand extends Command
{
    protected $signature = 'mango:sync-blacklist {--now : Запустить синхронизацию минуя очередь }';

    protected $description = 'Обновление черного списка номеров из Mango';

    public function handle(): int
    {
        if (!config('jobs.mango')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        if ($this->option('now')) {
            $this->runSynchronously();
        } else {
            $this->runInQueue();
        }

        return self::SUCCESS;
    }

    private function runSynchronously(): void
    {
        SyncMangoBlacklistJob::dispatchSync();
        $this->info('Синхронизация черного списка успешно выполнена.');
    }

    private function runInQueue(): void
    {
        SyncMangoBlacklistJob::dispatch();
        $this->info('Синхронизация черного списка успешно отправлена в очередь.');
    }
}
