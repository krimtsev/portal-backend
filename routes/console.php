<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/**
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
 */
Schedule::command('certificates:sync')->dailyAt('03:00');
Schedule::command('yclients:sync-company-stats')->dailyAt('04:00');
