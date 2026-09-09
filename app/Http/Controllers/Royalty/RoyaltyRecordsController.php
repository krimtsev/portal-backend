<?php

declare(strict_types=1);

namespace App\Http\Controllers\Royalty;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Royalty\RoyaltyRecordsRequest;
use App\Http\Resources\Royalty\RoyaltyRecordsResource;
use App\Responses\JsonResponse;
use App\Services\Royalty\RoyaltyRecordsService;

final class RoyaltyRecordsController extends Controller
{
    protected RoyaltyRecordsService $service;

    public function __construct(RoyaltyRecordsService $service)
    {
        $this->service = $service;
    }

    public function list(RoyaltyRecordsRequest $request)
    {
        $filters = $request->filters();
        $monthInput = DateHelper::parseMonthWithoutShift($filters['date']);
        $partnerId = (int) $filters['partner_id'];

        $report = $this->service->getRoyaltyReport($partnerId, $monthInput);

        return JsonResponse::Send([
            'list'   => RoyaltyRecordsResource::collection($report['list']),
            'totals' => $report['totals'],
        ]);
    }
}
