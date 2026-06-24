<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Requests\Statistics\StatisticsStaffRequest;
use App\Models\Partner\Partner;
use App\Models\Yclient\YcComment;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcStaffDailyStat;
use App\Models\Yclient\YcStaffTransaction;
use App\Models\Yclient\YcStorageTransaction;
use App\Responses\JsonResponse;
use Carbon\Carbon;

final class StaffStatisticsController
{
    public function list(StatisticsStaffRequest $request)
    {
        $date = $request->input('filters.date');
        $partnerId = $request->input('filters.partner_id');

        $monthInput = Carbon::parse($date);
        $startDateTime = $monthInput->copy()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
        $endDateTime = $monthInput->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');

        $pastStartDate = $monthInput->copy()->startOfMonth()->subDays(60)->startOfDay()->format('Y-m-d');

        $partner = Partner::find($partnerId);

        $staffList = YcStaffDailyStat::query()
            ->select('yc_staff_daily_stats.staff_id')
            ->leftJoin('yc_company_staff', function ($join) {
                $join->on('yc_staff_daily_stats.staff_id', '=', 'yc_company_staff.staff_id')
                    ->on('yc_staff_daily_stats.company_id', '=', 'yc_company_staff.company_id');
            })
            // Данные сотрудника
            ->selectRaw('MAX(yc_company_staff.name) as name')
            ->selectRaw('MAX(yc_company_staff.firstname) as firstname')
            ->selectRaw('MAX(yc_company_staff.surname) as surname')
            ->selectRaw('MAX(yc_company_staff.specialization) as specialization')
            // Финансовые и клиентские агрегаты за период
            ->selectRaw('SUM(income_total) as income_total')
            ->selectRaw('SUM(client_new) as client_new')
            ->selectRaw('ROUND(AVG(fullness_percent), 2) as fullness_percent')
            ->selectRaw('SUM(record_completed) as record_completed')
            ->where('yc_staff_daily_stats.company_id', $partner->yclients_id)
            ->whereBetween('yc_staff_daily_stats.date', [$startDateTime, $endDateTime])
            ->groupBy('yc_staff_daily_stats.staff_id')
            ->orderBy('income_total', 'desc')
            ->get();

        $ratingStats = YcComment::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('date', [$startDateTime, $endDateTime])
            ->select('staff_id')
            ->selectRaw('COUNT(*) as rating_total') // Всего оценок
            ->selectRaw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_best')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        $recordStats = YcRecord::query()
            ->where('yc_records.company_id', $partner->yclients_id)
            ->whereBetween('yc_records.datetime', [$startDateTime, $endDateTime])
            ->select('yc_records.staff_id')
            ->selectRaw('SUM(yc_records.total_tariff_cost) as total_tariff_cost')
            ->groupBy('yc_records.staff_id')
            ->get()
            ->keyBy('staff_id');

        $staffTransactionStats = YcStaffTransaction::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('date', [$startDateTime, $endDateTime])
            ->select('staff_id')
            ->selectRaw('SUM(CASE WHEN expense_id IN (6, 12) THEN ABS(amount) ELSE 0 END) as transaction_loyalty')
            ->selectRaw('SUM(CASE WHEN expense_id IN (7) THEN ABS(amount) ELSE 0 END) as transaction_sales')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        $storageTransactionStats = YcStorageTransaction::query()
            ->where('company_id', $partner->yclients_id)
            ->whereNotNull('master_id')
            ->whereBetween('create_date', [$startDateTime, $endDateTime])
            ->select('master_id')
            ->selectRaw('SUM(CASE WHEN loyalty_abonement_id IS NOT NULL AND loyalty_certificate_id IS NOT NULL THEN 1 ELSE 0 END) as transaction_count')
            ->groupBy('master_id')
            ->get()
            ->keyBy('master_id');

        $pastClientsByStaff = YcRecord::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('datetime', [$pastStartDate, $startDateTime])
            ->where('attendance', 1)
            ->whereNotNull('client_id')
            ->select('staff_id', 'client_id')
            ->get()
            ->groupBy('staff_id')
            ->map(function ($records) {
                return $records->pluck('client_id')->unique();
            });

        $currentClientsByStaff = YcRecord::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('datetime', [$startDateTime, $endDateTime])
            ->where('attendance', 1)
            ->whereNotNull('client_id')
            ->select('staff_id', 'client_id')
            ->get()
            ->groupBy('staff_id')
            ->map(function ($records) {
                return $records->pluck('client_id')->unique();
            });

        $staffList->transform(function ($staff) use (
            $ratingStats,
            $recordStats,
            $staffTransactionStats,
            $storageTransactionStats,
            $pastClientsByStaff,
            $currentClientsByStaff
        ) {
            $staffRatings = $ratingStats->get($staff->staff_id);
            $staffRecord = $recordStats->get($staff->staff_id);
            $staffTransaction = $staffTransactionStats->get($staff->staff_id);
            $staffStorageTransaction = $storageTransactionStats->get($staff->staff_id);

            // Валовая выручка
            $staff->income_total = (int) round($staff->income_total);

            // Всего отзывов
            $staff->rating_total = $staffRatings ? (int) $staffRatings->rating_total : 0;

            // Заполняемость
            $staff->fullness_percent = (int) round($staff->fullness_percent);

            // Отзывы у которых оценка 5
            $staff->rating_best = $staffRatings ? (int) $staffRatings->rating_best : 0;

            // Новые клиенты
            $staff->client_new = (int) $staff->client_new;

            // Дополнительные услуги
            $total_tariff_cost = $staffRecord ? (float) $staffRecord->total_tariff_cost : 0.00;
            $staff->additional_services = (int) round($total_tariff_cost);

            // Продажи без сертификатов
            $staff->transaction_sales = $staffTransaction ? (float) $staffTransaction->transaction_sales : 0.00;

            // Сумма дополнительных услуг и продаж
            $services_with_transactions = $staff->additional_services + $staff->transaction_sales;
            $staff->services_with_transactions = (int) round($services_with_transactions);

            // Продажи абонементов и сертификатов
            $staff->transaction_loyalty = $staffTransaction ? (float) $staffTransaction->transaction_loyalty : 0.00;

            // Средний чек
            $transaction_count = $staffStorageTransaction ? $staffStorageTransaction->transaction_count : 0;
            $record_completed = $staff->record_completed + $transaction_count;
            $staff->average_sum = $staff->record_completed > 0
                ? (int) round($staff->income_total / $record_completed)
                : 0;

            $pastClients = $pastClientsByStaff->get($staff->staff_id, collect());
            $currentClients = $currentClientsByStaff->get($staff->staff_id, collect());

            $pastClientsCount = $pastClients->count();
            $returnedClientsCount = $currentClients->intersect($pastClients)->count();

            // Записываем процент возвращаемости в объект сотрудника
            $staff->retention_percent = $pastClientsCount > 0
                ? (int) round(($returnedClientsCount / $pastClientsCount) * 100)
                : 0;

            return $staff;
        });

        return JsonResponse::Send([
            'list' => $staffList->toArray(),
        ]);
    }
}
