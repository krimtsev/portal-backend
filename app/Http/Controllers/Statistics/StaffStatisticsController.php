<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

use App\Constants\Statistics\StatisticsCache;
use App\Helpers\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\StatisticsTotalCompareRequest;
use App\Http\Requests\Statistics\StatisticsStaffDetailsRequest;
use App\Http\Requests\Statistics\StatisticsStaffRequest;
use App\Http\Resources\Statistics\StatisticsTotalCompareResource;
use App\Http\Resources\Statistics\StatisticsStaffCompareResource;
use App\Http\Resources\Statistics\StatisticsStaffDetailsResource;
use App\Http\Resources\Statistics\StatisticsStaffResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Statistics\StaffDetailsStatisticsService;
use App\Services\Statistics\StaffStatisticsService;
use App\Services\Statistics\StaffTotalStatisticsService;

final class StaffStatisticsController extends Controller
{
    public function __construct(
        private readonly StaffStatisticsService $staffStatisticsService,
        private readonly StaffTotalStatisticsService $staffTotalStatisticsService,
        private readonly StaffDetailsStatisticsService $staffDetailsStatisticsService,
    ) {}

    public function list(StatisticsStaffRequest $request)
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));
        $date = $request->input('filters.date');
        $companyId = (int) $partner->yclients_id;

        $stats = Cache::remember(
            "statistics_staff_list_{$companyId}_{$date}",
            now()->addHours(3),
            fn () => $this->staffStatisticsService->getMonthlyStats($partner, $date),
            StatisticsCache::YC_STATISTICS_TAG
        );

        return JsonResponse::Send([
            'list' => StatisticsStaffResource::collection($stats)->resolve(),
        ]);
    }

    public function compare(StatisticsStaffRequest $request)
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));
        $date = $request->input('filters.date');
        $companyId = (int) $partner->yclients_id;

        $stats = Cache::remember(
            "statistics_staff_compare_{$companyId}_{$date}",
            now()->addHours(3),
            fn () => $this->staffStatisticsService->getComparedMonthlyStats($partner, $date),
            StatisticsCache::YC_STATISTICS_TAG
        );

        return JsonResponse::Send([
            'list' => StatisticsStaffCompareResource::collection($stats)->resolve(),
        ]);
    }

    public function totalCompare(StatisticsTotalCompareRequest $request): \Illuminate\Http\JsonResponse
    {
        $partner = Partner::findOrFail($request->input('partner_id'));
        $date = $request->input('date');
        $companyId = (int) $partner->yclients_id;

        $stats = Cache::remember(
            "statistics_staff_total_compare_{$companyId}_{$date}",
            now()->addHours(3),
            fn () => $this->staffTotalStatisticsService->getMonthlyStats($partner, $date),
            StatisticsCache::YC_STATISTICS_TAG
        );

        return JsonResponse::Send([
            'data' => new StatisticsTotalCompareResource($stats),
        ]);
    }

    public function staffDetails(StatisticsStaffDetailsRequest $request)
    {
        $partner = Partner::findOrFail($request->input('partner_id'));
        $staffId = (int) $request->input('staff_id');
        $date = $request->input('date');
        $companyId = (int) $partner->yclients_id;

        $data = Cache::remember(
            "statistics_staff_details_{$companyId}_{$staffId}_{$date}",
            now()->addHours(3),
            fn () => $this->staffDetailsStatisticsService->getStaffDetails($partner, $staffId, $date),
            StatisticsCache::YC_STATISTICS_TAG
        );

        return JsonResponse::Send([
            'data' => (new StatisticsStaffDetailsResource($data))->resolve(),
        ]);
    }
}
