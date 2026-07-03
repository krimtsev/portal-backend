<?php

namespace App\Http\Resources\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsStaffDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $staff = $this->resource['staff'];
        $monthlyStats = $this->resource['monthly_stats'];
        $referenceDate = $this->resource['reference_date'];

        $currentStats = $monthlyStats[$referenceDate];

        /*$history = [
            'additional_services' => [],
            'average_sum' => [],
            'transaction_sales' => [],
        ];

        // 1. Формируем историю за выбранный месяц + 3 предыдущих
        for ($i = 0; $i < 4; $i++) {
            $currentMonth = $referenceDate->copy()->subMonths($i)->format('Y-m-d');
            $previousMonth = $referenceDate->copy()->subMonths($i + 1)->format('Y-m-d');

            $cStats = $monthlyStats[$currentMonth] ?? null;
            $pStats = $monthlyStats[$previousMonth] ?? null;

            $metrics = ['additional_services', 'average_sum', 'transaction_sales'];

            foreach ($metrics as $metric) {
                $currentVal = 0;
                $previousVal = 0;

                if ($metric === 'average_sum') {
                    $currentVal = $cStats && $cStats['work_days'] > 0 ? ($cStats['average_sum_total'] / $cStats['work_days']) : 0;
                    $previousVal = $pStats && $pStats['work_days'] > 0 ? ($pStats['average_sum_total'] / $pStats['work_days']) : 0;
                } else {
                    $currentVal = $cStats[$metric] ?? 0;
                    $previousVal = $pStats[$metric] ?? 0;
                }

                $history[$metric][$currentMonth] = [
                    'value' => (int) round($currentVal),
                    'percent' => $this->calculatePercent((float) $currentVal, (float) $previousVal),
                ];
            }
        }

        // 2. Сравнительная статистика для текущего месяца (по клиентам)
        $currentMonthKey = $referenceDate->format('Y-m-d');
        $prevMonthKey = $referenceDate->copy()->subMonth()->format('Y-m-d');

        $cStats = $monthlyStats[$currentMonthKey] ?? [];
        $pStats = $monthlyStats[$prevMonthKey] ?? [];
        */

        $fullnessPercent = !empty($currentStats['work_days'])
            ? round($currentStats['fullness_percent'] / $currentStats['work_days'])
            : 0;

        return [
            'id'             => $staff->staff_id,
            'name'           => $staff->name,
            'specialization' => $staff->specialization,
            'avatar_big'     => $staff->avatar_big,

            'client_new'       => $currentStats['client_new'],
            'client_return'    => $currentStats['client_return'],
            'client_active'    => $currentStats['client_active'],
            'fullness_percent' => $fullnessPercent,

            'month' => [

            ],

            'work_days' => $currentStats['work_days'],
            'date'      => $referenceDate,

            /*'history' => $history,
            'current_month' => [
                'fullness_percent' => $currentFullness,
                'client_total' => [
                    'value' => $cStats['client_total'] ?? 0,
                    'growth' => $this->calculateGrowth((float)($cStats['client_total'] ?? 0), (float)($pStats['client_total'] ?? 0))
                ],
                'client_return' => [
                    'value' => $cStats['client_return'] ?? 0,
                    'growth' => $this->calculateGrowth((float)($cStats['client_return'] ?? 0), (float)($pStats['client_return'] ?? 0))
                ],
                'client_new' => [
                    'value' => $cStats['client_new'] ?? 0,
                    'growth' => $this->calculateGrowth((float)($cStats['client_new'] ?? 0), (float)($pStats['client_new'] ?? 0))
                ]
            ]*/
        ];
    }

    /**
     * Стандартный расчет процента от прошлых месяцев, как в PartnerStatisticsService[cite: 3].
     */
    private function calculatePercent(float $current, float $previous): int
    {
        if ($previous > 0) {
            return (int) round((($current - $previous) / $previous) * 100);
        }

        if ($current > 0) {
            return 100;
        }

        return 0;
    }

    /**
     * Логика расчета роста (отличия в %) из StatisticsStaffCompareResource[cite: 2].
     */
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
