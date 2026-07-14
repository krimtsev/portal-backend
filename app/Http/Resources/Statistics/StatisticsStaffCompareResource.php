<?php

declare(strict_types=1);

namespace App\Http\Resources\Statistics;

use App\Helpers\MathHelper;
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
            'income_goods',
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
            'services_per_visit',
        ];

        $growth = [];

        foreach ($metricsToCompare as $key) {
            $currentVal = (float) ($currentData[$key] ?? 0.00);
            $prevVal = $prevData ? (float) ($prevData[$key] ?? 0.00) : 0.00;

            $growth[$key] = MathHelper::calculateGrowth($currentVal, $prevVal);
        }

        return [
            ...$currentData,
            'growth' => $growth,
        ];
    }
}
