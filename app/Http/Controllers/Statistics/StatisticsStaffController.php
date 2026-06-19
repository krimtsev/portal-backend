<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Requests\Statistics\StatisticsStaffRequest;
use App\Models\Partner\Partner;
use App\Models\Yclient\YcComment;
use App\Models\Yclient\YcRecordService;
use App\Models\Yclient\YcStaffDailyStat;
use App\Models\Yclient\YcStaffTransaction;
use App\Responses\JsonResponse;
use Carbon\Carbon;

final class StatisticsStaffController
{
    public function list(StatisticsStaffRequest $request)
    {
        $date = $request->input('filters.date');
        $partnerId = $request->input('filters.partner_id');

        // Вычисляем точные границы месяца
        $monthInput = Carbon::parse($date);
        $startDate = Carbon::parse($monthInput)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($monthInput)->endOfMonth()->format('Y-m-d');

        $startDateTimeStr = $monthInput->copy()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
        $endDateTimeStr = $monthInput->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');

        $partner = Partner::find($partnerId);

        // 1. Получаем агрегированную статистику по сотрудникам за период (теперь это базис)
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
            ->selectRaw('SUM(income_goods) as income_goods')
            ->selectRaw('SUM(income_services) as income_services')
            ->selectRaw('SUM(client_new) as client_new')
            ->selectRaw('SUM(client_return) as client_return')
            ->selectRaw('SUM(record_completed) as record_completed')
            ->selectRaw('ROUND(AVG(fullness_percent), 2) as fullness_percent')
            ->where('yc_staff_daily_stats.company_id', $partner->yclients_id)
            ->whereBetween('yc_staff_daily_stats.date', [$startDate, $endDate])
            ->groupBy('yc_staff_daily_stats.staff_id')
            ->orderBy('name')
            ->get();

        // 2. Получаем и агрегируем статистику оценок из комментариев
        $ratingStats = YcComment::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('staff_id')
            ->selectRaw('COUNT(*) as rating_total') // Всего оценок
            ->selectRaw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_best')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        // 3. Получаем транзакции (лояльность, продажи и количество чеков доп. услуг)
        $transactionStats = YcStaffTransaction::query()
            ->where('company_id', $partner->yclients_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('staff_id')
            ->selectRaw('SUM(CASE WHEN expense_id IN (6, 12) THEN ABS(amount) ELSE 0 END) as transaction_loyalty')
            ->selectRaw('SUM(CASE WHEN expense_id IN (7) THEN ABS(amount) ELSE 0 END) as transaction_sales')
            ->selectRaw('SUM(CASE WHEN expense_id = 6 THEN 1 ELSE 0 END) as additional_sales_count')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        // 4. Агрегируем стоимость дополнительных услуг из локальной истории записей
        $recordServiceStats = YcRecordService::query()
            ->join('yc_records', 'yc_record_services.record_id', '=', 'yc_records.record_id')
            ->where('yc_records.company_id', $partner->yclients_id)
            ->whereBetween('yc_records.datetime', [$startDateTimeStr, $endDateTimeStr])
            ->select('yc_records.staff_id')
            ->selectRaw('SUM(yc_record_services.manual_cost) as total_manual_cost')
            ->selectRaw('SUM(yc_record_services.tariff_cost) as total_tariff_cost')
            ->selectRaw('SUM(yc_record_services.base_tariff_cost) as total_base_tariff_cost')
            ->selectRaw('SUM(CASE WHEN yc_record_services.base_tariff_cost > 0 THEN yc_record_services.tariff_cost ELSE 0 END) as additional_services_total')
            ->groupBy('yc_records.staff_id')
            ->get()
            ->keyBy('staff_id');

        // 4. Сопоставляем все собранные данные со списком сотрудников
        $staffList->transform(function ($staff) use ($ratingStats, $transactionStats, $recordServiceStats) {
            $staffRatings = $ratingStats->get($staff->staff_id);
            $staffTransactions = $transactionStats->get($staff->staff_id);
            $staffServices = $recordServiceStats->get($staff->staff_id);

            // Метрики уже вычислены в основном SQL запросе, делаем строгую типизацию
            $staff->income_total     = (float) $staff->income_total;
            $staff->income_goods     = (float) $staff->income_goods;
            $staff->income_services  = (float) $staff->income_services;
            $staff->client_new       = (int) $staff->client_new;
            $staff->client_return    = (int) $staff->client_return;
            $staff->fullness_percent = (float) $staff->fullness_percent;

            // --- КОРРЕКТНЫЙ РАСЧЕТ СРЕДНЕГО ЧЕКА ---
            $totalRecords = (int) $staff->record_completed;
            $additionalSalesCount = $staffTransactions ? (int) $staffTransactions->additional_sales_count : 0;
            $totalDenominator = $totalRecords + $additionalSalesCount;

            $staff->average_sum = $totalDenominator > 0
                ? (int) round($staff->income_total / $totalDenominator, 0, PHP_ROUND_HALF_UP)
                : 0;

            // Метрики рейтинга
            $staff->rating_total = $staffRatings ? (int) $staffRatings->rating_total : 0;
            $staff->rating_best  = $staffRatings ? (int) $staffRatings->rating_best : 0;

            // Метрики транзакций
            $staff->transaction_loyalty = $staffTransactions ? (float) $staffTransactions->transaction_loyalty : 0.0;
            $staff->transaction_sales   = $staffTransactions ? (float) $staffTransactions->transaction_sales : 0.0;

            // Доп. услуги и финальная сумма
            $staff->additional_services = $staffServices ? (float) $staffServices->additional_services_total : 0.0;
            $staff->sum = round($staff->additional_services + $staff->transaction_sales, 2);

            return $staff;
        });

        return JsonResponse::Send([
            'list' => $staffList->toArray(),
        ]);
    }
}
