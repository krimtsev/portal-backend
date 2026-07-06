<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Models\Partner\Partner;
use App\Models\Yclient\YcComment;
use App\Models\Yclient\YcCompanyStat;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordGoodsTransaction;
use App\Models\Yclient\YcStaffStat;
use App\Models\Yclient\YcStaffTransaction;
use App\Models\Yclient\YcStaffWorkDay;
use App\Models\Yclient\YcStorageTransaction;
use App\Models\Yclient\YcTransaction;
use Carbon\Carbon;
use DB;
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
        $staffTransactions = $this->getTransactionStats($companyId, $startDateTime, $endDateTime);
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

    public function getTotalCompare(Partner $partner, \Illuminate\Support\Carbon $date): ?YcCompanyStat
    {
        $companyId = (int) $partner->yclients_id;

        if (!$companyId) {
            return null;
        }

        $startDate = $date->copy()->startOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d H:i:s');

        /** @var YcCompanyStat|null $stat */
        $stat = YcCompanyStat::forCompany($companyId)
            ->dailyForPeriod($startDate, $endDate)
            ->where('fullness_percent', '>', 0)
            ->selectRaw('
                SUM(income_total) as income_total,
                SUM(income_goods) as income_goods,
                ROUND(AVG(fullness_percent), 2) as fullness_percent,
                ROUND(AVG(income_average), 2) as average_sum,
                SUM(client_new) as client_new,
                SUM(client_return) as client_return
            ')
            ->first();

        $attendedCount = YcRecord::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where('attendance', 1)
            ->count();

        $storageTransactionCount = YcStorageTransaction::query()
            ->where('company_id', $companyId)
            ->whereNotNull('master_id')
            ->whereBetween('create_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('loyalty_abonement_id', '>', 0)
                    ->orWhere('loyalty_certificate_id', '>', 0);
            })
            ->count();

        $standaloneGoodsCount = YcRecordGoodsTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('record_staff_id')
                    ->orWhereColumn('record_staff_id', '!=', 'master_id')
                    ->orWhere('attendance', '!=', 1);
            })
            ->distinct('record_id')
            ->count('record_id');

        $totalRecords = $attendedCount + $storageTransactionCount + $standaloneGoodsCount;

        if ($stat) {
            $endDateTime = $date->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
            $currentPeriod = $date->copy()->startOfMonth()->startOfDay()->subMonths(2)->startOfDay();
            $currentPeriodStart = $date->copy()->startOfMonth()->subMonths(2)->startOfDay()->format('Y-m-d H:i:s');
            $pastPeriodEnd = $currentPeriod->copy()->subDay()->endOfDay()->format('Y-m-d H:i:s');
            $pastPeriodStart = $currentPeriod->copy()->subDays(60)->startOfDay()->format('Y-m-d H:i:s');

            // Получаем уникальных клиентов компании за периоды
            $companyPastClients = $this->getCompanyClientsByPeriod($companyId, $pastPeriodStart, $pastPeriodEnd);
            $companyCurrentClients = $this->getCompanyClientsByPeriod($companyId, $currentPeriodStart, $endDateTime);

            $stat->retention_percent = $companyPastClients->isNotEmpty()
                ? round(($companyCurrentClients->intersect($companyPastClients)->count() / $companyPastClients->count()) * 100, 2)
                : 0;

            $stat->average_sum = $totalRecords > 0
                ? (int) round($stat->income_total / $totalRecords)
                : 0;
        }

        return $stat;
    }

    private function getStaffBaseList(int $companyId, string $start, string $end): Collection
    {
        return YcStaffStat::query()
            ->select('yc_staff_stats.staff_id')
            ->leftJoin('yc_company_staff', function ($join) {
                $join->on('yc_staff_stats.staff_id', '=', 'yc_company_staff.staff_id')
                    ->on('yc_staff_stats.company_id', '=', 'yc_company_staff.company_id');
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
            ->where('yc_staff_stats.company_id', $companyId)
            ->whereBetween('yc_staff_stats.start_date', [$start, $end])
            ->groupBy('yc_staff_stats.staff_id')
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

    private function getTransactionStats(int $companyId, string $start, string $end): Collection
    {
        $storageMasters = DB::table('yc_storage_transactions')
            ->select('document_id', DB::raw('MAX(master_id) as master_id'))
            ->where('company_id', $companyId)
            ->groupBy('document_id');

        return YcTransaction::query()
            ->from('yc_transactions as t')
            ->leftJoin('yc_records as r', 't.record_id', '=', 'r.record_id')
            ->leftJoinSub($storageMasters, 'st', function ($join) {
                $join->on('t.document_id', '=', 'st.document_id')
                    ->where('t.record_id', 0);
            })
            ->where('t.company_id', $companyId)
            ->whereBetween('t.date', [$start, $end])
            ->selectRaw('COALESCE(t.master_id, r.staff_id, st.master_id) as master_id')
            ->selectRaw('SUM(CASE WHEN t.expense_id IN (6, 12) THEN ABS(t.amount) ELSE 0 END) as transaction_loyalty')
            ->selectRaw('SUM(CASE WHEN t.expense_id IN (7) THEN ABS(t.amount) ELSE 0 END) as transaction_sales')
            ->groupByRaw('COALESCE(t.master_id, r.staff_id, st.master_id)')
            ->get()
            ->filter(fn ($stat) => !is_null($stat->master_id))
            ->keyBy('master_id');
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

    private function getCompanyClientsByPeriod(int $companyId, string $start, string $end): Collection
    {
        return YcRecord::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$start, $end])
            ->where('attendance', 1)
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique();
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
