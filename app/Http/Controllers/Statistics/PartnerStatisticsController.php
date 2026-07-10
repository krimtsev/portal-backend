<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

use App\Constants\Statistics\StatisticsCache;
use App\Helpers\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\StatisticsPartnerRequest;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Statistics\PartnerStatisticsService;

final class PartnerStatisticsController extends Controller
{
    public function __construct(
        private readonly PartnerStatisticsService $statisticsService
    ) {}

    /**
     * Статистика по филиалам (dashboard)
     */
    public function income(StatisticsPartnerRequest $request): \Illuminate\Http\JsonResponse
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));
        $date = $request->input('filters.date');
        $monthsCount = (int) $request->input('filters.months_count', 6);

        $companyId = (int) $partner->yclients_id;

        $stats = Cache::remember(
            "statistics_partner_income_{$companyId}_{$date}_{$monthsCount}_months",
            now()->addDay(),
            fn () => $this->statisticsService->getMonthlyIncomeStats($partner, $date, $monthsCount),
            StatisticsCache::YC_STATISTICS_TAG
        );

        return JsonResponse::Send([
            'list' => $stats,
        ]);
    }
}
