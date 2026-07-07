<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Models\Partner\Partner;
use App\Models\Yclients\YcComment;
use App\Models\Yclients\YcRecord;
use App\Models\Yclients\YcStaffStat;
use App\Models\Yclients\YcStaffWorkDay;
use App\Models\Yclients\YcTransaction;
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

        $workDaysStats = $this->getWorkDaysStats($companyId, $startDateTime, $endDateTime);

        $pastClients = $this->getClientsByPeriod($companyId, $pastPeriodStart, $pastPeriodEnd);
        $currentClients = $this->getClientsByPeriod($companyId, $currentPeriodStart, $endDateTime);

        return $staffList->map(callback: function ($staff) use (
            $ratingStats,
            $recordStats,
            $staffTransactions,
            $workDaysStats,
            $pastClients,
            $currentClients,
        ) {
            $staffId = $staff->staff_id;

            $staff->ratings = $ratingStats->get($staffId);
            $staff->records = $recordStats->get($staffId);

            $staff->transactions = $staffTransactions->get($staffId);

            $staff->work_days = $workDaysStats->get($staffId);

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

    private function getStaffBaseList(int $companyId, string $start, string $end): Collection
    {
        // Переводим даты в чистый формат Y-m-d для работы со скоупом
        $startDate = Carbon::parse($start)->format('Y-m-d');
        $endDate = Carbon::parse($end)->format('Y-m-d');

        return YcStaffStat::query()
            ->select([
                'yc_staff_stats.staff_id',
                'yc_staff_stats.company_id',
                'yc_staff_stats.income_total',
                'yc_staff_stats.income_average',
                'yc_staff_stats.client_new',
                'yc_staff_stats.client_return',
                'yc_staff_stats.fullness_percent',
                'yc_staff_stats.record_completed',
            ])
            ->leftJoin('yc_company_staff', function ($join) {
                $join->on('yc_staff_stats.staff_id', '=', 'yc_company_staff.staff_id')
                    ->on('yc_staff_stats.company_id', '=', 'yc_company_staff.company_id');
            })
            ->selectRaw('yc_company_staff.name as name')
            ->selectRaw('yc_company_staff.firstname as firstname')
            ->selectRaw('yc_company_staff.surname as surname')
            ->selectRaw('yc_company_staff.specialization as specialization')
            ->selectRaw('yc_company_staff.avatar as avatar')
            ->where('yc_staff_stats.company_id', $companyId)
            ->monthlyForPeriod($startDate, $endDate)
            ->orderBy('yc_staff_stats.income_total', 'desc')
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
        $servicesSub = DB::table('yc_record_services')
            ->select('record_id', DB::raw('SUM(amount) as services_count'))
            ->groupBy('record_id');

        return YcRecord::query()
            ->leftJoinSub($servicesSub, 'services_sub', 'yc_records.record_id', '=', 'services_sub.record_id')
            ->where('yc_records.company_id', $companyId)
            ->whereBetween('yc_records.datetime', [$start, $end])
            ->select('yc_records.staff_id')
            ->selectRaw('SUM(yc_records.total_tariff_cost) as total_tariff_cost')
            ->selectRaw('SUM(CASE WHEN yc_records.attendance = 1 THEN 1 ELSE 0 END) as attended_count')
            ->selectRaw('SUM(CASE WHEN yc_records.attendance = 1 THEN COALESCE(services_sub.services_count, 0) ELSE 0 END) as services_count')
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

    /**
     * Получение количества рабочих дней (смен) сотрудников за период.
     */
    private function getWorkDaysStats(int $companyId, string $start, string $end): Collection
    {
        return YcStaffWorkDay::query()
            ->where('company_id', $companyId)
            ->where(function ($query) {
                $query->where('has_schedule', true)
                    ->orWhere('has_records', true);
            })
            ->whereBetween('date', [$start, $end])
            ->select('staff_id')
            ->selectRaw('COUNT(*) as work_days_count')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');
    }
}
