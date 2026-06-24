<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCertificatesJob;
use Illuminate\Console\Command;

final class SyncCertificatesCommand extends Command
{
    protected $signature = 'certificates:sync {--now : Запустить синхронизацию минуя очередь }';

    protected $description = 'Обновление сертификатов из Google Sheets';

    public function handle(): int
    {
        if (!config('jobs.certificates')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        match ($this->option('now')) {
            true  => $this->runSynchronously(),
            false => $this->runInQueue(),
        };

        return self::SUCCESS;
    }

    private function runSynchronously(): void
    {
        UpdateCertificatesJob::dispatchSync();
        $this->info('Синхронизация сертификатов успешно выполнена (в текущем процессе).');
    }

    private function runInQueue(): void
    {
        UpdateCertificatesJob::dispatch();
        $this->info('Задача на синхронизацию сертификатов успешно отправлена в очередь.');
    }
}
