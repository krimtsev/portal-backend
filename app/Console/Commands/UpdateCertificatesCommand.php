<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateCertificatesJob;

class UpdateCertificatesCommand extends Command
{
    protected $signature = 'certificates:update';
    protected $description = 'Обновление сертификатов из Google Sheets';

    public function handle(): void
    {
        UpdateCertificatesJob::dispatch();

        $this->info('Job на обновление сертификатов поставлена в очередь.');
    }
}
