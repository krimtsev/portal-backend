<?php

declare(strict_types=1);

namespace App\Services\Formatters;

use App\Helpers\PhoneNumber;
use Illuminate\Support\Carbon;

final class MangoCallFormatter
{
    public static function formatDailyReport(array $reports, Carbon $date): string
    {
        $lines = [];
        $lines[] = sprintf('📞 %s', __('reports.daily_missed_call.title', ['date' => $date->format('d.m.Y')]));
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

    public static function formatMissedCall(
        string $partnerName,
        string $callerNumber,
        string $clientName,
        string $callDateTime,
        string $duration,
    ): string {
        $message = [
            __('reports.missed_call.title'),
            __('reports.missed_call.branch', ['branch' => "<b>{$partnerName}</b>"]),
            __('reports.missed_call.caller', [
                'phone' => PhoneNumber::format($callerNumber),
                'name'  => $clientName ? "<b>({$clientName})</b>" : '',
            ]),
            __('reports.missed_call.datetime', ['datetime' => $callDateTime]),
            __('reports.missed_call.duration', ['duration' => $duration]),
        ];

        return implode("\n", $message);
    }
}
