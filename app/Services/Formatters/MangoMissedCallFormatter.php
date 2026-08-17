<?php

declare(strict_types=1);

namespace App\Services\Formatters;

use Illuminate\Support\Carbon;

final class MangoMissedCallFormatter
{
    public static function formatDailyReport(array $reports, Carbon $date): string
    {
        $lines = [];
        $lines[] = __('reports.daily_missed_call.title', ['date' => $date->format('d.m.Y')]);
        $lines[] = '';

        foreach ($reports as $report) {
            $lines[] = __('reports.daily_missed_call.branch', ['branch' => "<b>{$report['branch']}</b>"]);
            $lines[] = __('reports.daily_missed_call.stats', [
                'accepted' => "<b>{$report['stats']['accepted']}</b>",
                'missed'   => "<b>{$report['stats']['missed']}</b>",
                'total'    => "<b>{$report['stats']['total']}</b>",
            ]);
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }
}
