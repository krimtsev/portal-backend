<?php

namespace App\Jobs;

use App\Http\Tasks\Sheet\UpdateCertificatesTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCertificatesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $service = app(UpdateCertificatesTask::class);
        $service->update();
    }

    public function uniqueId(): string
    {
        return 'update-certificates';
    }
}
