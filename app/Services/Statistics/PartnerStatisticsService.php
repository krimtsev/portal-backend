<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Models\Partner\Partner;
use App\Models\User\User;
use App\Models\Yclient\YcCompanyDailyStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class PartnerStatisticsService
{
    /**
     * Получает статистику доходов компании за динамическое количество месяцев от указанной даты.
     */
    public function getMonthlyIncomeStats(Partner $partner, string $date, int $monthsCount = 6): array
    {
        $companyId = (int) $partner->yclients_id;

        if (!$companyId) {
            return [];
        }

        /*        return Cache::remember(
                    "company_finances_{$companyId}_{$date}_{$monthsCount}_months",
                    now()->addHours(2),
                    fn () => $this->calculateStats($companyId, $date, $monthsCount)
                );*/

        return $this->calculateStats($companyId, $date, $monthsCount);
    }

    /**
     * Статистика по партнеру за последние 4 месяца
     * используется на главной странице портала
     */
    public function getPartnerFinanceService(User $user): array
    {
        $user->loadMissing('partner');
        $yclientsId = $user->partner?->yclients_id;

        if (!$yclientsId) {
            return [];
        }

        $companyId = (int) $yclientsId;

        return Cache::remember(
            "company_finances_{$companyId}_last_4_months",
            now()->addHours(2),
            function () use ($companyId) {
                $referenceDate = now()->day === 1 ? now()->subDays(2) : now();
                $dateString = $referenceDate->subMonth()->format('Y-m-01');

                return $this->calculateStats($companyId, $dateString, 4);
            }
        );
    }

    private function calculateStats(int $companyId, string $date, int $monthsCount): array
    {
        $referenceDate = Carbon::parse($date);

        // Нам нужно выгрузить на 1 месяц больше данных, чем запрашивается,
        // чтобы рассчитать процент изменения (разницу) для самого старого месяца.
        $endDate = $referenceDate->copy()->endOfMonth();
        $startDate = $referenceDate->copy()->subMonths($monthsCount)->startOfMonth();

        $stats = YcCompanyDailyStat::forCompany($companyId)
            ->forPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
            ->get(['date', 'income_total']);

        $monthlyTotals = [];
        foreach ($stats as $stat) {
            $monthKey = Carbon::parse($stat->date)->startOfMonth()->format('Y-m-d');
            $monthlyTotals[$monthKey] = ($monthlyTotals[$monthKey] ?? 0.0) + $stat->income_total;
        }

        $result = [];

        for ($i = 0; $i < $monthsCount; $i++) {
            $currentMonth = $referenceDate->copy()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $previousMonth = $referenceDate->copy()->subMonths($i + 1)->startOfMonth()->format('Y-m-d');

            $currentTotal = $monthlyTotals[$currentMonth] ?? 0.0;
            $previousTotal = $monthlyTotals[$previousMonth] ?? 0.0;

            $result[$currentMonth] = [
                'income_total' => (int) round($currentTotal),
                'percent'      => $this->calculatePercent($currentTotal, $previousTotal),
            ];
        }

        return $result;
    }

    /**
     * Вычисляет процент изменения между текущим и прошлым месяцем.
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

    public function getMonthlyCompanyStats(Partner $partner, Carbon $date): ?YcCompanyDailyStat
    {
        $companyId = (int) $partner->yclients_id;

        if (!$companyId) {
            return null;
        }

        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');

        return YcCompanyDailyStat::forCompany($companyId)
            ->forPeriod($startDate, $endDate)
            ->where('fullness_percent', '>', 0)
            ->selectRaw('
                SUM(income_total) as income_total,
                SUM(income_goods) as income_goods,
                SUM(income_services) as income_services,
                ROUND(AVG(fullness_percent), 2) as fullness_percent,
                SUM(record_completed) as record_completed,
                SUM(record_pending) as record_pending,
                SUM(record_canceled) as record_canceled,
                SUM(record_total) as record_total,
                SUM(client_new) as client_new,
                SUM(client_return) as client_return,
                SUM(client_active) as client_active,
                SUM(client_lost) as client_lost,
                SUM(client_total) as client_total
            ')
            ->first();
    }
}
