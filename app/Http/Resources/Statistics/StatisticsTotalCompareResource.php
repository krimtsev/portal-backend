<?php

declare(strict_types=1);

namespace App\Http\Resources\Statistics;

use App\Helpers\MathHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsTotalCompareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentData = $this->formatStats($this->resource);

        $prevData = $this->previous_stats
            ? $this->formatStats($this->previous_stats)
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

    /**
     * Форматирование только аналитических данных по компании (без персональных данных сотрудников)
     */
    private function formatStats($stat): array
    {
        if (!$stat) {
            return [];
        }

        return [
            'income_total'      => (float) ($stat->income_total ?? 0.00),
            'income_goods'      => (float) ($stat->income_goods ?? 0.00),
            'client_new'        => (int) ($stat->client_new ?? 0),
            'client_return'     => (int) ($stat->client_return ?? 0),
            'fullness_percent'  => (int) round((float) ($stat->fullness_percent ?? 0)),
            'retention_percent' => (int) round((float) ($stat->retention_percent ?? 0)),
            'average_sum'       => (int) ($stat->average_sum ?? 0),

            'rating_total'               => (int) ($stat->rating_total ?? 0),
            'rating_best'                => (int) ($stat->rating_best ?? 0),
            'additional_services'        => (float) ($stat->additional_services ?? 0.00),
            'transaction_sales'          => (float) ($stat->transaction_sales ?? 0.00),
            'services_with_transactions' => (float) ($stat->services_with_transactions ?? 0.00),
            'transaction_loyalty'        => (float) ($stat->transaction_loyalty ?? 0.00),
            'work_days_count'            => (int) ($stat->work_days_count ?? 0),
            'services_per_visit'         => (float) ($stat->services_per_visit ?? 0.00),
        ];
    }
}
