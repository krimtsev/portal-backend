<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Helpers\DateHelper;
use App\Helpers\MathHelper;
use App\Models\Partner\Partner;
use App\Models\User\User;
use App\Models\Yclients\YcCompanyStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class StatisticsPartnerService
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
        $referenceDate = DateHelper::parseMonthWithoutShift($date);

        $endDate = $referenceDate->copy()->endOfMonth()->format('Y-m-d');
        $startDate = $referenceDate->copy()->subMonths($monthsCount)->startOfMonth()->format('Y-m-d');

        $stats = YcCompanyStat::forCompany($companyId)
            ->monthlyForPeriod($startDate, $endDate)
            ->get(['start_date', 'income_total']);

        $monthlyTotals = [];
        foreach ($stats as $stat) {
            $monthKey = Carbon::parse($stat->start_date)->startOfMonth()->format('Y-m-d');
            $monthlyTotals[$monthKey] = ($monthlyTotals[$monthKey] ?? 0.0) + $stat->income_total;
        }

        $result = [];

        for ($i = 0; $i < $monthsCount; $i++) {
            $currentMonth = $referenceDate->copy()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $previousMonth = $referenceDate->copy()->subMonths($i + 1)->startOfMonth()->format('Y-m-d');

            $currentTotal = $monthlyTotals[$currentMonth] ?? 0.0;
            $previousTotal = $monthlyTotals[$previousMonth] ?? 0.0;

            $result[$currentMonth] = [
                'value'   => (int) round($currentTotal),
                'percent' => MathHelper::calculatePercent($currentTotal, $previousTotal),
            ];
        }

        return $result;
    }
}
