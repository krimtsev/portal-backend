<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\StatisticsCompanyRequest;
use App\Http\Requests\Statistics\StatisticsPartnerRequest;
use App\Http\Resources\Statistics\StatisticsCompanyResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Statistics\PartnerStatisticsService;
use Illuminate\Support\Carbon;

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

        $monthsCount = (int) $request->input('filters.months_count', 6);

        $stats = $this->statisticsService->getMonthlyIncomeStats(
            $partner,
            $request->input('filters.date'),
            $monthsCount
        );

        return JsonResponse::Send([
            'list' => $stats,
        ]);
    }

    public function company(StatisticsCompanyRequest $request): \Illuminate\Http\JsonResponse
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));
        $date = Carbon::parse($request->input('filters.date'));

        $stats = $this->statisticsService->getMonthlyCompanyStats(
            $partner,
            $date
        );

        return JsonResponse::Send([
            'data' => new StatisticsCompanyResource($stats)
        ]);
    }
}
