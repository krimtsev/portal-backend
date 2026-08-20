<?php

declare(strict_types=1);

namespace App\Services\Formatters;

use App\Helpers\PhoneNumber;
use Illuminate\Support\Carbon;

final class MangoCallFormatter
{
    public static function formatDailyReport(array $reports, Carbon $date): string
    {
        $lines = [
            sprintf('📞 %s', __('reports.daily_missed_call.title', ['date' => $date->format('d.m.Y')])),
            '',
            __('reports.daily_missed_call.header'),
            '',
        ];

        foreach ($reports as $report) {
            $lines[] = __('reports.daily_missed_call.item', [
                'branch'   => "<b>{$report['branch']}</b>",
                'accepted' => $report['stats']['accepted'],
                'missed'   => $report['stats']['missed'],
                'total'    => $report['stats']['total'],
            ]);
        }

        return implode("\n", $lines);
    }

    public static function formatMissedCall(
        string $partnerName,
        string $callerNumber,
        ?string $clientName,
        string $callDateTime,
        string $duration,
    ): string {
        $message = [
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
