<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\StatisticsTotalCompareRequest;
use App\Http\Requests\Statistics\StatisticsPartnerRequest;
use App\Http\Resources\Statistics\StatisticsTotalCompareResource;
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
}
