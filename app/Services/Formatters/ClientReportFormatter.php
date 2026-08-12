<?php

declare(strict_types=1);

namespace App\Services\Formatters;

use App\Enums\Report\ClientReportType;
use App\Models\Partner\Partner;
use Illuminate\Support\Carbon;

final readonly class ClientReportFormatter
{
    public static function format(
        Partner $partner,
        ClientReportType $type,
        Carbon $targetDate,
        int $days,
        bool $isEmpty = false
    ): string {
        $daysText = trans_choice('reports.clients.days', $days);
        $periodText = __('reports.clients.period', ['count' => $daysText]);

        $lines = [
            sprintf('<b>%s:</b> %s', __('reports.clients.title'), e($type->label())),
            sprintf('<b>%s:</b> %s', __('reports.clients.branch'), e($partner->name)),
            sprintf('<b>%s:</b> %s (%s)', __('reports.clients.date'), $targetDate->format('Y-m-d'), $periodText),
        ];

        if ($isEmpty) {
            $lines[] = '';
            $lines[] = sprintf('<i>%s</i>', __('reports.clients.empty'));
        }

        return implode("\n", $lines);
    }
}
