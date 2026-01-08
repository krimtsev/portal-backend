<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateCertificatesJob;

class UpdateCertificatesCommand extends Command
{
    protected $signature = 'certificates:update {--sync}';
    protected $description = 'Обновление сертификатов из Google Sheets';

    public function handle(): void
    {
        if ($this->option('sync')) {
            UpdateCertificatesJob::dispatchSync();
            $this->info('Job на обновление сертификатов запущена.');
        } else {
            UpdateCertificatesJob::dispatch();
            $this->info('Job на обновление сертификатов поставлена в очередь.');
        }
    }
}
