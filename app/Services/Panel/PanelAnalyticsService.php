<?php

declare(strict_types=1);

namespace App\Services\Panel;

use App\Enums\Ticket\TicketState;
use App\Models\Partner\Partner;
use App\Models\Ticket\Ticket;
use App\Services\Royalty\RoyaltyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class PanelAnalyticsService
{
    public function __construct(
        private RoyaltyService $royaltyService
    ) {}

    /**
     * Получить сводную статистику по заявкам и партнерам.
     *
     * @return array<string, mixed>
     */
    public function getSummaryStats(bool $withJobs = false): array
    {
        $stateCounts = Ticket::query()
            ->select('state', DB::raw('count(*) as aggregate'))
            ->groupBy('state')
            ->pluck('aggregate', 'state');

        $totalCount = $stateCounts->sum();
        $newCount = $stateCounts->get(TicketState::New->value ?? 'new', 0);
        $inProgressCount = $stateCounts->get(TicketState::InProgress->value ?? 'in_progress', 0);
        $waitingCount = $stateCounts->get(TicketState::Waiting->value ?? 'waiting', 0);

        $periods = $this->getPeriodsMetrics();
        $efficiency = $this->getEfficiencyMetrics($totalCount);
        $partnerStats = $this->getPartnerStats();
        $royaltyStats = $this->getRoyaltyStats();
        $jobsStats = $withJobs ? $this->getJobsStats() : null;

        return [
            'partners'      => $partnerStats,
            'total_count'   => $totalCount,
            'new_count'     => $newCount,
            'pending_count' => $inProgressCount + $waitingCount,
            'periods'       => $periods,
            'efficiency'    => $efficiency,
            'royalty'       => $royaltyStats,
            'jobs'          => $jobsStats,
        ];
    }

    /**
     * Расчет статистики по партнерам (всего, активные, отключенные).
     *
     * @return array{total_count: int, active_count: int, disabled_count: int}
     */
    private function getPartnerStats(): array
    {
        $stats = Partner::query()
            ->selectRaw('
                COUNT(*) as total_count,
                COUNT(CASE WHEN disabled = false THEN 1 END) as active_count,
                COUNT(CASE WHEN disabled = true THEN 1 END) as disabled_count
            ')
            ->first();

        return [
            'total_count'    => (int) ($stats->total_count ?? 0),
            'active_count'   => (int) ($stats->active_count ?? 0),
            'disabled_count' => (int) ($stats->disabled_count ?? 0),
        ];
    }

    /**
     * Расчет динамики поступления заявок за 1, 7 и 30 дней.
     */
    private function getPeriodsMetrics(): array
    {
        $now = Carbon::now();

        // Границы для 1 дня
        $todayStart = $now->copy()->startOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();

        // Границы для 7 дней
        $sub7Days = $now->copy()->subDays(7);
        $sub14Days = $now->copy()->subDays(14);

        // Границы для 30 дней
        $sub30Days = $now->copy()->subDays(30);
        $sub60Days = $now->copy()->subDays(60);

        $counts = Ticket::query()
            ->selectRaw('
                    COUNT(CASE WHEN created_at >= ? THEN 1 END) as day_1_current,
                    COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) as day_1_previous,

                    COUNT(CASE WHEN created_at >= ? THEN 1 END) as day_7_current,
                    COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) as day_7_previous,

                    COUNT(CASE WHEN created_at >= ? THEN 1 END) as day_30_current,
                    COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) as day_30_previous
                ', [
                $todayStart, $yesterdayStart, $todayStart,
                $sub7Days, $sub14Days, $sub7Days,
                $sub30Days, $sub60Days, $sub30Days,
            ])
            ->first();

        return [
            '1_day' => [
                'current'  => (int) ($counts->day_1_current ?? 0),
                'previous' => (int) ($counts->day_1_previous ?? 0),
            ],
            '7_days' => [
                'current'  => (int) ($counts->day_7_current ?? 0),
                'previous' => (int) ($counts->day_7_previous ?? 0),
            ],
            '30_days' => [
                'current'  => (int) ($counts->day_30_current ?? 0),
                'previous' => (int) ($counts->day_30_previous ?? 0),
            ],
        ];
    }

    /**
     * Расчет эффективности.
     */
    private function getEfficiencyMetrics(int $totalCount): array
    {
        $successState = TicketState::Success->value;

        $successCount = Ticket::query()
            ->where('state', $successState)
            ->count();

        $successRate = $totalCount > 0
            ? round(($successCount / $totalCount) * 100, 2)
            : 0.0;

        $closureStates = [
            TicketState::Closed->value,
            TicketState::Cancel->value,
            $successState,
        ];

        $avgMinutes = Ticket::query()
            ->whereIn('state', $closureStates)
            ->whereNotNull('updated_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_duration')
            ->value('avg_duration');

        return [
            'success_rate_percentage'      => $successRate,
            'average_closure_time_minutes' => $avgMinutes !== null
                ? round((float) $avgMinutes, 1)
                : 0,
        ];
    }

    private function getRoyaltyStats(): array
    {
        $results = [];
        $now = Carbon::now()->subMonth();

        for ($i = 0; $i < 6; $i++) {
            $monthInput = $now->copy()->subMonths($i)->startOfMonth();
            $startDate = $monthInput->format('Y-m-d');
            $endDate = $monthInput->copy()->endOfMonth()->format('Y-m-d');

            $partners = $this->royaltyService->getPartnersWithStatsQuery($startDate, $endDate)->get();

            $processedCollection = $this->royaltyService->transform($partners, $monthInput);

            $incomeTotal = 0;
            $royaltyTotal = 0;

            foreach ($processedCollection as $item) {
                $incomeTotal += $item['gross_revenue'] ?? $item['income_total'] ?? 0;
                $royaltyTotal += $item['royalty_amount'] ?? 0;
            }

            $results[] = [
                'month'          => $monthInput->format('Y-m'),
                'income_total'   => (int) round($incomeTotal),
                'royalty_amount' => (int) round($royaltyTotal),
            ];
        }

        return array_reverse($results);
    }

    private function getJobsStats(): array
    {
        $totalJobs = DB::table('jobs')->count();

        $queueCounts = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as aggregate'))
            ->whereIn('queue', ['default', 'yclients'])
            ->groupBy('queue')
            ->pluck('aggregate', 'queue');

        $failedJobs = DB::table('failed_jobs')->count();

        return [
            'total_count'    => $totalJobs,
            'default_count'  => (int) $queueCounts->get('default', 0),
            'yclients_count' => (int) $queueCounts->get('yclients', 0),
            'failed_count'   => $failedJobs,
        ];
    }
}
