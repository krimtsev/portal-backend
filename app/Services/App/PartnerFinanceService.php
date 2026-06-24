<?php

declare(strict_types=1);

namespace App\Services\App;

use App\Models\User\User;
use App\Models\Yclient\YcCompanyDailyStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class PartnerFinanceService
{
    /**
     * Получает статистику доходов за последние 4 полных месяца.
     *
     * @return array<string, array{income_total: float, percent: float}>
     */
    public function getMonthlyIncomeStats(User $user): array
    {
        $user->loadMissing('partner');
        $yclientsId = $user->partner?->yclients_id;

        if (!$yclientsId) {
            return [];
        }

        $companyId = (int) $yclientsId;

        /*        return Cache::remember(
                    "partner_finances_{$companyId}_last_4_months",
                    now()->addHours(2),
                    fn () => $this->calculateStats($companyId)
                );*/

        return $this->calculateStats($companyId);
    }

    private function calculateStats(int $companyId): array
    {
        $referenceDate = now()->day === 1 ? now()->subDays(2) : now();

        $endDate = $referenceDate->copy()->subMonth()->endOfMonth();
        $startDate = $referenceDate->copy()->subMonths(5)->startOfMonth();

        $stats = YcCompanyDailyStat::forCompany($companyId)
            ->forPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
            ->get(['date', 'income_total']);

        $monthlyTotals = [];
        foreach ($stats as $stat) {
            $monthKey = Carbon::parse($stat->date)->startOfMonth()->format('Y-m-d');
            $monthlyTotals[$monthKey] = ($monthlyTotals[$monthKey] ?? 0.0) + $stat->income_total;
        }

        $result = [];

        for ($i = 1; $i <= 4; $i++) {
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
     * Вычисляет процент изменения и округляет до целого числа.
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
}
