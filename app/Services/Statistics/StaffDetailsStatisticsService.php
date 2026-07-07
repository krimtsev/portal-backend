<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Helpers\MathHelper;
use App\Models\Partner\Partner;
use App\Models\Yclients\YcCompanyStaff;
use App\Models\Yclients\YcRecord;
use App\Models\Yclients\YcStaffStat;
use App\Models\Yclients\YcStorageTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class StaffDetailsStatisticsService
{
    public function getStaffDetails(Partner $partner, int $staffId, string $date): array
    {
        $referenceDate = Carbon::parse($date)->startOfMonth();
        $companyId = (int) $partner->yclients_id;

        $startDate = $referenceDate->copy()->subMonths(5)->startOfMonth()->format('Y-m-d');
        $endDate = $referenceDate->copy()->endOfMonth()->format('Y-m-d');

        $staff = $this->getCompanyStaff($companyId, $staffId);
        $monthlyStats = $this->getStaffMonthlyStat($companyId, $staffId, $startDate, $endDate);
        $recordsStats = $this->getRecordsStats($companyId, $staffId, $startDate, $endDate);

        $monthlyTotals = [];

        for ($i = 0; $i <= 5; $i++) {
            $monthKey = $referenceDate->copy()->subMonths($i)->format('Y-m-d');

            $monthlyRow = $monthlyStats->get($monthKey);
            $record = $recordsStats->get($monthKey);

            $monthlyTotals[$monthKey] = [
                'client_new'          => (int) ($monthlyRow->client_new ?? 0),
                'client_return'       => (int) ($monthlyRow->client_return ?? 0),
                'client_active'       => (int) ($monthlyRow->client_active ?? 0),
                'income_goods'        => (int) ($monthlyRow->income_goods ?? 0),
                'fullness_percent'    => (float) ($monthlyRow->fullness_percent ?? 0), // Берем напрямую из источника
                'additional_services' => (float) ($record->additional_services ?? 0),
                'average_sum'         => (float) ($monthlyRow->income_average ?? 0),  // Берем готовый средний чек из источника
            ];
        }

        $history = [
            'additional_services' => [],
            'average_sum'         => [],
            'income_goods'        => [],
        ];

        for ($i = 0; $i < 4; $i++) {
            $currentMonth = $referenceDate->copy()->subMonths($i)->format('Y-m-d');
            $previousMonth = $referenceDate->copy()->subMonths($i + 1)->format('Y-m-d');

            $currentStats = $monthlyTotals[$currentMonth];
            $previousStats = $monthlyTotals[$previousMonth];

            foreach (array_keys($history) as $metric) {
                $currentVal = $currentStats[$metric];
                $previousVal = $previousStats[$metric];

                $history[$metric][$currentMonth] = [
                    'value'   => (int) round($currentVal),
                    'percent' => MathHelper::calculateGrowth($currentVal, $previousVal),
                ];
            }
        }

        return [
            'staff'          => $staff,
            'monthly_stats'  => $monthlyTotals,
            'history'        => $history,
            'reference_date' => $referenceDate->format('Y-m-d'),
        ];
    }

    private function getCompanyStaff(int $companyId, int $staffId): YcCompanyStaff
    {
        return YcCompanyStaff::where('staff_id', $staffId)
            ->where('company_id', $companyId)
            ->firstOrFail();
    }

    private function getStaffMonthlyStat(int $companyId, int $staffId, string $startDate, string $endDate): Collection
    {
        return YcStaffStat::forCompany($companyId)
            ->where('staff_id', $staffId)
            ->monthlyForPeriod($startDate, $endDate)
            ->get()
            ->keyBy(function (YcStaffStat $stat) {
                return Carbon::parse($stat->start_date)->format('Y-m-d');
            });
    }

    private function getRecordsStats(int $companyId, int $staffId, string $startDate, string $endDate): Collection
    {
        return YcRecord::query()
            ->where('company_id', $companyId)
            ->where('staff_id', $staffId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(datetime, '%Y-%m-01') as month_key")
            ->selectRaw('SUM(total_tariff_cost) as additional_services')
            ->selectRaw('SUM(CASE WHEN attendance = 1 THEN 1 ELSE 0 END) as attended_count')
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');
    }
}
