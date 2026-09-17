<?php

declare(strict_types=1);

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Пример использования
 *
 * $stats = QueryProfiler::profile('compare execution breakdown', fn () =>
 *      Cache::remember(
 *          "statistics_staff_compare_{$companyId}_{$date}",
 *          now()->addHours(3),
 *          fn () => $this->staffStatisticsService->getComparedMonthlyStats($partner, $date),
 *          StatisticsCache::YC_STATISTICS_TAG
 *      )
 * );
 */

final class QueryProfiler
{
    /**
     * Замеряет время выполнения и профилирует SQL-запросы внутри $callback.
     *
     * @template T
     * @param string $label Метка для лога
     * @param Closure(): T $callback Выполняемый код
     * @param int $take Количество самых медленных запросов
     * @return T
     */
    public static function profile(string $label, Closure $callback, int $take = 10): mixed
    {
        DB::enableQueryLog();
        $startTime = microtime(true);

        $result = $callback();

        $executionTime = round(microtime(true) - $startTime, 2);

        $slowQueries = collect(DB::getQueryLog())
            ->sortByDesc('time')
            ->take($take)
            ->map(fn ($q) => [
                'time_ms'  => $q['time'],
                'query'    => $q['query'],
                'bindings' => $q['bindings'],
            ])
            ->values()
            ->toArray();

        Log::info("[SQL Profile] {$label}", [
            'total_time_sec'  => $executionTime,
            'slowest_queries' => $slowQueries,
        ]);

        return $result;
    }
}
