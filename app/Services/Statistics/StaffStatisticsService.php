<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Models\Partner\Partner;
use App\Models\Yclient\YcComment;
use App\Models\Yclient\YcCompanyStaff;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordGoodsTransaction;
use App\Models\Yclient\YcStaffDailyStat;
use App\Models\Yclient\YcStaffTransaction;
use App\Models\Yclient\YcStaffWorkDay;
use App\Models\Yclient\YcStorageTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class StaffStatisticsService
{
    public function getMonthlyStats(Partner $partner, string $date): Collection
    {
        $monthInput = Carbon::parse($date);
        $startDateTime = $monthInput->copy()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
        $endDateTime = $monthInput->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');

        $currentPeriod = $monthInput->copy()->startOfMonth()->startOfDay()->subMonths(2)->startOfDay();
        $currentPeriodStart = $monthInput->copy()->startOfMonth()->subMonths(2)->startOfDay()->format('Y-m-d H:i:s');
        $pastPeriodEnd = $currentPeriod->copy()->subDay()->endOfDay()->format('Y-m-d H:i:s');
        $pastPeriodStart = $currentPeriod->copy()->subDays(60)->startOfDay()->format('Y-m-d H:i:s');

        $companyId = (int) $partner->yclients_id;

        $staffList = $this->getStaffBaseList($companyId, $startDateTime, $endDateTime);
        $ratingStats = $this->getRatingStats($companyId, $startDateTime, $endDateTime);
        $recordStats = $this->getRecordStats($companyId, $startDateTime, $endDateTime);
        $staffTransactions = $this->getStaffTransactionStats($companyId, $startDateTime, $endDateTime);
        $storageTransactions = $this->getStorageTransactionStats($companyId, $startDateTime, $endDateTime);

        $goodsStats = $this->getGoodsTransactionStats($companyId, $startDateTime, $endDateTime);

        $workDaysStats = $this->getWorkDaysStats($companyId, $startDateTime, $endDateTime);

        $pastClients = $this->getClientsByPeriod($companyId, $pastPeriodStart, $pastPeriodEnd);
        $currentClients = $this->getClientsByPeriod($companyId, $currentPeriodStart, $endDateTime);

        return $staffList->map(callback: function ($staff) use (
            $ratingStats,
            $recordStats,
            $staffTransactions,
            $storageTransactions,
            $goodsStats,
            $workDaysStats,
            $pastClients,
            $currentClients,
        ) {
            $staffId = $staff->staff_id;

            $staff->ratings = $ratingStats->get($staffId);
            $staff->records = $recordStats->get($staffId);
            $staff->transactions = $staffTransactions->get($staffId);
            $staff->storage_transactions = $storageTransactions->get($staffId);

            $staff->goods_stats = $goodsStats->get($staffId);

            $staff->work_days = $workDaysStats->get($staffId);

            // Расчет возвращаемости
            $staffPast = $pastClients->get($staffId, collect());
            $staffCurrent = $currentClients->get($staffId, collect());

            $staff->retention_percent = $staffPast->isNotEmpty()
                ? round(($staffCurrent->intersect($staffPast)->count() / $staffPast->count()) * 100, 2)
                : 0;

            return $staff;
        });
    }

    public function getComparedMonthlyStats(Partner $partner, string $date): Collection
    {
        // Получаем статистику за запрашиваемый месяц
        $currentStats = $this->getMonthlyStats($partner, $date);

        // Вычисляем дату для прошлого месяца
        $prevDate = Carbon::parse($date)->subMonth()->format('Y-m-d');

        // Получаем статистику за прошлый месяц и индексируем по staff_id для быстрого поиска
        $prevStats = $this->getMonthlyStats($partner, $prevDate)->keyBy('staff_id');

        // Добавляем данные прошлого месяца как свойство к текущим объектам
        return $currentStats->map(function ($staff) use ($prevStats) {
            $staff->previous_stats = $prevStats->get($staff->staff_id);

            return $staff;
        });
    }

    public function getStaffDetails(Partner $partner, int $staffId, string $date): array
    {
        $referenceDate = Carbon::parse($date)->startOfMonth();
        $companyId = (int) $partner->yclients_id;

        $endDate = $referenceDate->copy()->endOfMonth()->format('Y-m-d');
        $startDate = $referenceDate->copy()->subMonths(4)->startOfMonth()->format('Y-m-d');

        // Сотрудник
        $staff = YcCompanyStaff::where('staff_id', $staffId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $stats = YcStaffDailyStat::forCompany($companyId)
            ->where('staff_id', $staffId)
            ->forPeriod($startDate, $endDate)
            ->get();

        $monthlyTotals = [];

        foreach ($stats as $stat) {
            $monthKey = Carbon::parse($stat->date)->startOfMonth()->format('Y-m-d');

            if (!isset($monthlyTotals[$monthKey])) {
                $monthlyTotals[$monthKey] = [
                    'client_new'       => 0,
                    'client_return'    => 0,
                    'client_active'    => 0,
                    'fullness_percent' => 0.00,
                    'work_days'        => 0,
                ];
            }

            $monthlyTotals[$monthKey]['client_new'] += $stat->client_new;
            $monthlyTotals[$monthKey]['client_return'] += $stat->client_return;
            $monthlyTotals[$monthKey]['client_active'] += $stat->client_active;
            $monthlyTotals[$monthKey]['fullness_percent'] += $stat->fullness_percent;
            $monthlyTotals[$monthKey]['work_days']++;

            /*            $monthlyTotals[$monthKey]['additional_services'] += $stat->additional_services ?? 0;
                        $monthlyTotals[$monthKey]['transaction_sales'] += $stat->transaction_sales ?? 0;
                        $monthlyTotals[$monthKey]['average_sum_total'] += $stat->average_sum ?? 0;
                        $monthlyTotals[$monthKey]['fullness_percent_total'] += $stat->fullness_percent ?? 0;
                        $monthlyTotals[$monthKey]['client_total'] += $stat->client_total ?? 0;
                        $monthlyTotals[$monthKey]['client_return'] += $stat->client_return ?? 0;
                        $monthlyTotals[$monthKey]['client_new'] += $stat->client_new ?? 0;

                        if (($stat->fullness_percent ?? 0) > 0 || ($stat->income_total ?? 0) > 0) {
                            $monthlyTotals[$monthKey]['work_days']++;
                        }*/
        }

        return [
            'staff'          => $staff,
            'monthly_stats'  => $monthlyTotals,
            'reference_date' => $referenceDate->format('Y-m-d'),
        ];
    }

    private function getStaffBaseList(int $companyId, string $start, string $end): Collection
    {
        return YcStaffDailyStat::query()
            ->select('yc_staff_daily_stats.staff_id')
            ->leftJoin('yc_company_staff', function ($join) {
                $join->on('yc_staff_daily_stats.staff_id', '=', 'yc_company_staff.staff_id')
                    ->on('yc_staff_daily_stats.company_id', '=', 'yc_company_staff.company_id');
            })
            ->selectRaw('MAX(yc_company_staff.name) as name')
            ->selectRaw('MAX(yc_company_staff.firstname) as firstname')
            ->selectRaw('MAX(yc_company_staff.surname) as surname')
            ->selectRaw('MAX(yc_company_staff.specialization) as specialization')
            ->selectRaw('MAX(yc_company_staff.avatar) as avatar')
            ->selectRaw('SUM(income_total) as income_total')
            ->selectRaw('SUM(client_new) as client_new')
            ->selectRaw('SUM(client_return) as client_return')
            ->selectRaw('ROUND(AVG(fullness_percent), 2) as fullness_percent')
            ->selectRaw('SUM(record_completed) as record_completed')
            ->where('yc_staff_daily_stats.company_id', $companyId)
            ->whereBetween('yc_staff_daily_stats.date', [$start, $end])
            ->groupBy('yc_staff_daily_stats.staff_id')
            ->orderBy('income_total', 'desc')
            ->get();
    }

    private function getRatingStats(int $companyId, string $start, string $end): Collection
    {
        return YcComment::query()
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->select('staff_id')
            ->selectRaw('COUNT(*) as rating_total')
            ->selectRaw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_best')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');
    }

    private function getRecordStats(int $companyId, string $start, string $end): Collection
    {
        return YcRecord::query()
            ->where('yc_records.company_id', $companyId)
            ->whereBetween('yc_records.datetime', [$start, $end])
            ->select('yc_records.staff_id')
            ->selectRaw('SUM(yc_records.total_tariff_cost) as total_tariff_cost')
            ->selectRaw('SUM(CASE WHEN yc_records.attendance = 1 THEN 1 ELSE 0 END) as attended_count')
            ->groupBy('yc_records.staff_id')
            ->get()
            ->keyBy('staff_id');
    }

    private function getStaffTransactionStats(int $companyId, string $start, string $end): Collection
    {
        return YcStaffTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->select('staff_id')
            ->selectRaw('SUM(CASE WHEN expense_id IN (6, 12) THEN ABS(amount) ELSE 0 END) as transaction_loyalty')
            ->selectRaw('SUM(CASE WHEN expense_id IN (7) THEN ABS(amount) ELSE 0 END) as transaction_sales')
            ->selectRaw('SUM(CASE WHEN expense_id IN (8) THEN 1 ELSE 0 END) as transaction_other_income')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');
    }

    private function getStorageTransactionStats(int $companyId, string $start, string $end): Collection
    {
        return YcStorageTransaction::query()
            ->where('company_id', $companyId)
            ->whereNotNull('master_id')
            ->whereBetween('create_date', [$start, $end])
            ->where(function ($query) {
                $query->where('loyalty_abonement_id', '>', 0)
                    ->orWhere('loyalty_certificate_id', '>', 0);
            })
            ->select('master_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('master_id')
            ->get()
            ->keyBy('master_id');
    }

    private function getClientsByPeriod(int $companyId, string $start, string $end): Collection
    {
        return YcRecord::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$start, $end])
            ->where('attendance', 1)
            ->whereNotNull('client_id')
            ->select('staff_id', 'client_id')
            ->get()
            ->groupBy('staff_id')
            ->map(fn ($records) => $records->pluck('client_id')->unique());
    }

    private function getGoodsTransactionStats(int $companyId, string $start, string $end): Collection
    {
        return YcRecordGoodsTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$start, $end])
            ->select('master_id')
            ->selectRaw('
                COUNT(DISTINCT CASE
                    WHEN record_staff_id IS NULL
                         OR record_staff_id != master_id
                         OR attendance != 1
                    THEN record_id
                END) as standalone_goods_count
            ')
            ->groupBy('master_id')
            ->get()
            ->keyBy('master_id');
    }

    /**
     * Получение количества рабочих дней (смен) сотрудников за период.
     */
    private function getWorkDaysStats(int $companyId, string $start, string $end): Collection
    {
        return YcStaffWorkDay::query()
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->select('staff_id')
            ->selectRaw('COUNT(*) as work_days_count')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');
    }
}
