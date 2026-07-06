<?php

declare(strict_types=1);

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
        $workDays = $currentStats['work_days'];
        $history = $this->resource['history'];

        $fullnessPercent = $workDays > 0
            ? round($currentStats['fullness_percent'] / $workDays)
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

            'history'          => $history,

            'work_days' => $currentStats['work_days'],
            'date'      => $referenceDate,
        ];
    }
}
