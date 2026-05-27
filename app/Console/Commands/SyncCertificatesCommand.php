<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCertificatesJob;
use Illuminate\Console\Command;

class SyncCertificatesCommand extends Command
{
    protected $signature = 'certificates:sync {--now : Запустить синхронизацию минуя очередь }';

    protected $description = 'Обновление сертификатов из Google Sheets';

    public function handle(): void
    {
        match ($this->option('now')) {
            true  => $this->runSynchronously(),
            false => $this->runInQueue(),
        };
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
