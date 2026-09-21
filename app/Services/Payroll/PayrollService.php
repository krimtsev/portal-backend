<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\Partner\Partner;
use App\Models\Yclients\YcCompanyStaff;
use App\Models\Yclients\YcStorageTransaction;
use App\Models\Yclients\YcTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class PayrollService
{
    /**
     * Расчет оборотов и процента изменений для переданного списка партнеров
     *
     * @param Collection<int, Partner>|array<int, Partner> $partners
     * @return Collection<int, Partner>
     */
    public function calculateTurnoverForPartners(
        Collection|array $partners,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $partnersCollection = collect($partners);

        $yclientsIds = $partnersCollection
            ->pluck('yclients_id')
            ->filter()
            ->values()
            ->toArray();

        if (empty($yclientsIds)) {
            return $partnersCollection;
        }

        // Вычисляем прошлый период той же длительности
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        $pastEndDate = $startDate->copy()->subDay();
        $pastStartDate = $pastEndDate->copy()->subDays($daysDiff - 1);

        // Получаем суммы за оба периода
        $currentTurnovers = $this->getTurnovers($yclientsIds, $startDate, $endDate);
        $pastTurnovers = $this->getTurnovers($yclientsIds, $pastStartDate, $pastEndDate);

        // Навешиваем данные на модели
        return $partnersCollection->map(function (Partner $partner) use ($currentTurnovers, $pastTurnovers) {
            $current = $currentTurnovers[$partner->yclients_id] ?? 0.0;
            $past = $pastTurnovers[$partner->yclients_id] ?? 0.0;

            $partner->current_value = $current;
            $partner->past_value = $past;
            $partner->percent_difference = $this->calculatePercentDifference($current, $past);

            return $partner;
        });
    }

    /**
     * Расчет текущего оборота для одного партнера без прошлых периодов.
     *
     * @param Partner $partner
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function calculateTurnoverForPartner(Partner $partner, Carbon $startDate, Carbon $endDate): float
    {
        if (empty($partner->yclients_id)) {
            return 0.0;
        }

        $turnovers = $this->getTurnovers([$partner->yclients_id], $startDate, $endDate);

        return (float) ($turnovers[$partner->yclients_id] ?? 0.0);
    }

    private function getTurnovers(array $yclientsIds, Carbon $start, Carbon $end): array
    {
        /**
         * YcTransaction expense_id
         * 5 - Оказание услуг
         * 6 - Продажа абонементов
         * 7 - Продажа товаров
         * 12 - Продажа сертификатов
         */
        $transactions = YcTransaction::whereIn('company_id', $yclientsIds)
            ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
            ->whereIn('expense_id', [5, 6, 7, 12])
            ->groupBy('company_id')
            ->selectRaw('company_id, SUM(amount) as total')
            ->pluck('total', 'company_id')
            ->toArray();

        /**
         * YcStorageTransaction type_id
         * 1 - Продажа товара
         */
        $storage = YcStorageTransaction::whereIn('company_id', $yclientsIds)
            ->whereBetween('create_date', [$start->startOfDay(), $end->endOfDay()])
            ->where('type_id', 1)
            ->groupBy('company_id')
            ->selectRaw('company_id, SUM(cost) as total')
            ->pluck('total', 'company_id')
            ->toArray();

        $result = [];
        foreach ($yclientsIds as $id) {
            $result[$id] = (float) ($transactions[$id] ?? 0) + (float) ($storage[$id] ?? 0);
        }

        return $result;
    }

    private function calculatePercentDifference(float $current, float $past): float
    {
        if ($past === 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $past) / $past) * 100, 2);
    }

    /**
     * Расчет ЗП сотрудников по обороту для одного филиала
     *
     * @param string $yclientsId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function calculatePayrollForStaff(string $yclientsId, Carbon $startDate, Carbon $endDate): Collection
    {
        $transactions = YcTransaction::query()
            ->where('company_id', $yclientsId)
            ->whereBetween('date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->where('expense_id', 3)
            ->whereNotNull('master_id')
            ->selectRaw(
                'master_id,
                DATE(date) as day,
                ABS(SUM(amount)) as daily_sum'
            )
            ->groupBy('master_id', 'day')
            ->get();

        if ($transactions->isEmpty()) {
            return collect();
        }

        $groupedTransactions = [];
        $masterTotals = [];

        foreach ($transactions as $tx) {
            $groupedTransactions[$tx->master_id][$tx->day] = (float) $tx->daily_sum;
            $masterTotals[$tx->master_id] = ($masterTotals[$tx->master_id] ?? 0) + (float) $tx->daily_sum;
        }

        $masterIds = array_keys($groupedTransactions);

        $staff = YcCompanyStaff::query()
            ->whereIn('staff_id', $masterIds)
            ->select('staff_id', 'name', 'avatar', 'specialization')
            ->get();

        return $staff->map(function ($employee) use ($groupedTransactions, $masterTotals) {
            $employee->total_sum = $masterTotals[$employee->staff_id] ?? 0;
            $employee->daily_sums = $groupedTransactions[$employee->staff_id] ?? (object)[];
            return $employee;
        })
            ->sortBy('name')
            ->values();
    }
}
