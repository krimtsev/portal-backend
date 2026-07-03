<?php

namespace database\scripts;

use Illuminate\Support\Facades\Artisan;

/**
 * Запуск патча:
 * php artisan tinker database/scripts/yclients_full_sync.php
 */
$commands = [
    'yclients:sync-staff-work-days',
    'yclients:sync-company-stats',
    'yclients:sync-records',
    'yclients:sync-comments',
    'yclients:sync-storage-transactions',
    'yclients:sync-staff-stats',
    'yclients:sync-staff-transactions',
    'yclients:sync-transactions',
];

$months = ['2026-01', '2026-02', '2026-03', '2026-04', '2026-05', '2026-06'];

foreach ($commands as $command) {
    foreach ($months as $month) {
        echo "Running {$command} for {$month}...\n";
        $resultCode = Artisan::call($command, [
            '--month' => $month
        ]);

        if ($resultCode === 0) {
            echo "✅ Success: {$command} for {$month}\n";
        } else {
            echo "❌ Error (Code {$resultCode}) on {$command} for {$month}\n";
            echo Artisan::output() . "\n";
        }
    }
}
