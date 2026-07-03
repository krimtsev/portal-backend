<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statistics\StatisticsStaffDetailsRequest;
use App\Http\Requests\Statistics\StatisticsStaffRequest;
use App\Http\Resources\Statistics\StatisticsStaffCompareResource;
use App\Http\Resources\Statistics\StatisticsStaffDetailsResource;
use App\Http\Resources\Statistics\StatisticsStaffResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Statistics\StaffStatisticsService;

final class StaffStatisticsController extends Controller
{
    public function __construct(
        private readonly StaffStatisticsService $statisticsService
    ) {}

    public function list(StatisticsStaffRequest $request)
    {
        $partner = Partner::findOrFail($request->input('filters.partner_id'));

        $stats = $this->statisticsService->getMonthlyStats(
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

        $stats = $this->statisticsService->getComparedMonthlyStats(
            $partner,
            $request->input('filters.date')
        );

        return JsonResponse::Send([
            'list' => StatisticsStaffCompareResource::collection($stats)->resolve(),
        ]);
    }

    public function staffDetails(StatisticsStaffDetailsRequest $request)
    {
        $partner = Partner::findOrFail($request->input('partner_id'));
        $staffId = (int) $request->input('staff_id');

        $data = $this->statisticsService->getStaffDetails(
            $partner,
            $staffId,
            $request->input('date')
        );

        return JsonResponse::Send([
            'data' => (new StatisticsStaffDetailsResource($data))->resolve(),
        ]);
    }
}
