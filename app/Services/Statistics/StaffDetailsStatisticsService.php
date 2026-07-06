<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Helpers\MathHelper;
use App\Models\Partner\Partner;
use App\Models\Yclient\YcComment;
use App\Models\Yclient\YcCompanyStaff;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordGoodsTransaction;
use App\Models\Yclient\YcStaffStat;
use App\Models\Yclient\YcStaffTransaction;
use App\Models\Yclient\YcStaffWorkDay;
use App\Models\Yclient\YcStorageTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class StaffDetailsStatisticsService
{
    public function getStaffDetails(Partner $partner, int $staffId, string $date): array
    {
        $referenceDate = Carbon::parse($date)->startOfMonth();
        $companyId = (int) $partner->yclients_id;

        $startDate = $referenceDate->copy()->subMonths(5)->startOfMonth()->format('Y-m-d H:i:s');
        $endDate = $referenceDate->copy()->endOfMonth()->format('Y-m-d H:i:s');

        // Сотрудник
        $staff = $this->getCompanyStaff($companyId, $staffId);
        $dailyStats = $this->getStaffDailyStat($companyId, $staffId, $startDate, $endDate);
        $recordsStats = $this->getRecordsStats($companyId, $staffId, $startDate, $endDate);
        $storageStats = $this->getStorageStats($companyId, $staffId, $startDate, $endDate);

        $monthlyTotals = [];

        for ($i = 0; $i <= 5; $i++) {
            $monthKey = $referenceDate->copy()->subMonths($i)->format('Y-m-d');

            $daily = $dailyStats->get($monthKey);
            $record = $recordsStats->get($monthKey);
            $storage = $storageStats->get($monthKey);

            $incomeTotal = (float) ($daily->income_total ?? 0);
            $attendedCount = (int) ($record->attended_count ?? 0);
            $storageCount = (int) ($storage->transaction_count ?? 0);
            $totalRecords = $attendedCount + $storageCount;

            $monthlyTotals[$monthKey] = [
                'client_new'          => (int) ($daily->client_new ?? 0),
                'client_return'       => (int) ($daily->client_return ?? 0),
                'client_active'       => (int) ($daily->client_active ?? 0),
                'income_goods'        => (int) ($daily->income_goods ?? 0),
                'work_days'           => (int) ($daily->work_days ?? 0),
                'fullness_percent'    => (float) ($daily->fullness_percent_total ?? 0),
                'additional_services' => (float) ($record->additional_services ?? 0),
                'average_sum'         => $totalRecords > 0 ? ($incomeTotal / $totalRecords) : 0.0,

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

    private function getCompanyStaff(int $companyId, int $staffId): Collection
    {
        return YcCompanyStaff::where('staff_id', $staffId)
            ->where('company_id', $companyId)
            ->firstOrFail();
    }

    private function getStaffDailyStat(int $companyId, int $staffId, string $startDate, string $endDate): Collection
    {
        return YcStaffStat::forCompany($companyId)
            ->where('staff_id', $staffId)
            ->dailyForPeriod($startDate, $endDate)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m-01') as month_key")
            ->selectRaw('SUM(client_new) as client_new')
            ->selectRaw('SUM(client_return) as client_return')
            ->selectRaw('SUM(client_active) as client_active')
            ->selectRaw('SUM(fullness_percent) as fullness_percent_total')
            ->selectRaw('SUM(income_total) as income_total')
            ->selectRaw('SUM(income_goods) as income_goods')
            ->selectRaw('COUNT(CASE WHEN fullness_percent > 0 OR income_total > 0 THEN 1 END) as work_days')
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');
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

    private function getStorageStats(int $companyId, int $staffId, string $startDate, string $endDate): Collection
    {
        return YcStorageTransaction::query()
            ->where('company_id', $companyId)
            ->where('master_id', $staffId)
            ->whereBetween('create_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('loyalty_abonement_id', '>', 0)
                    ->orWhere('loyalty_certificate_id', '>', 0);
            })
            ->selectRaw("DATE_FORMAT(create_date, '%Y-%m-01') as month_key")
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');
    }
}
