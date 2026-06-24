<?php

namespace database\scripts;

use Illuminate\Support\Facades\Artisan;

/**
 * Запуск патча:
 * php artisan tinker database/scripts/yclients_sync.php
 */
$commands = [
    'yclients:sync-comments',
    'yclients:sync-staff-stats',
    'yclients:sync-transactions',
    'yclients:sync-staff-transactions',
    'yclients:sync-records',
    'yclients:sync-storage-transactions',
];

$months = ['2026-01', '2026-02', '2026-03', '2026-04', '2026-05'];
$companyId = 41120;

foreach ($commands as $command) {
    foreach ($months as $month) {
        echo "Running {$command} for {$month}...\n";
        Artisan::call($command, [
            '--month'      => $month,
            '--company_id' => $companyId,
        ]);
    }
}
