<?php

namespace App\Http\Resources\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsStaffCompareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentData = (new StatisticsStaffResource($this->resource))->toArray($request);

        $prevData = $this->previous_stats
            ? (new StatisticsStaffResource($this->previous_stats))->toArray($request)
            : null;

        $metricsToCompare = [
            'income_total',
            'fullness_percent',
            'client_new',
            'client_return',
            'retention_percent',
            'rating_total',
            'rating_best',
            'additional_services',
            'transaction_sales',
            'services_with_transactions',
            'transaction_loyalty',
            'average_sum',
            'work_days_count',
        ];

        $growth = [];

        foreach ($metricsToCompare as $key) {
            $currentVal = (float) ($currentData[$key] ?? 0);
            $prevVal = $prevData ? (float) ($prevData[$key] ?? 0) : 0;

            $growth[$key] = $this->calculateGrowth($currentVal, $prevVal);
        }

        return [
            ...$currentData,
            'growth' => $growth,
        ];
    }

    private function calculateGrowth(float $current, float $previous): ?int
    {
        if ($current === 0 && $previous === 0) {
            return 0;
        }

        if ($current >= $previous) {
            if ($current === 0) {
                return 0;
            }

            return (int) round((1 - ($previous / $current)) * 100);
        }

        if ($previous === 0) {
            return 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
