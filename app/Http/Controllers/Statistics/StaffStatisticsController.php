<?php

declare(strict_types=1);

namespace App\Http\Controllers\Statistics;

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
use Illuminate\Support\Carbon;

final class StaffStatisticsController extends Controller
{
    public function __construct(
        private readonly StaffStatisticsService $staffStatisticsService,
        private readonly StaffDetailsStatisticsService $staffDetailsStatisticsService,
    ) {}

    public function list(StatisticsStaffRequest $request)
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));

        $stats = $this->staffStatisticsService->getMonthlyStats(
            $partner,
            $request->input('filters.date')
        );

        return JsonResponse::Send([
            'list' => StatisticsStaffResource::collection($stats)->resolve(),
        ]);
    }

    public function compare(StatisticsStaffRequest $request)
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));

        $stats = $this->staffStatisticsService->getComparedMonthlyStats(
            $partner,
            $request->input('filters.date')
        );

        return JsonResponse::Send([
            'list' => StatisticsStaffCompareResource::collection($stats)->resolve(),
        ]);
    }

    public function totalCompare(StatisticsTotalCompareRequest $request): \Illuminate\Http\JsonResponse
    {
        $partner = Partner::findOrFail($request->input('partner_id'));
        $date = Carbon::parse($request->input('date'));

        $stats = $this->staffStatisticsService->getTotalCompare(
            $partner,
            $date
        );

        return JsonResponse::Send([
            'data' => new StatisticsTotalCompareResource($stats),
        ]);
    }

    public function staffDetails(StatisticsStaffDetailsRequest $request)
    {
        $partner = Partner::findOrFail($request->input('partner_id'));
        $staffId = (int) $request->input('staff_id');

        $data = $this->staffDetailsStatisticsService->getStaffDetails(
            $partner,
            $staffId,
            $request->input('date')
        );

        return JsonResponse::Send([
            'data' => (new StatisticsStaffDetailsResource($data))->resolve(),
        ]);
    }
}
