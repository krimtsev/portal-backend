<?php

declare(strict_types=1);

namespace App\Http\Controllers\Royalty;

use App\Helpers\DateHelper;
use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Royalty\RoyaltyListRequest;
use App\Http\Resources\Royalty\RoyaltyListResource;
use App\Responses\JsonResponse;
use App\Services\Royalty\RoyaltyService;

final class RoyaltyController extends Controller
{
    protected RoyaltyService $royaltyService;

    public function __construct(RoyaltyService $royaltyService)
    {
        $this->royaltyService = $royaltyService;
    }

    public function list(RoyaltyListRequest $request)
    {

        $filters = $request->filters();

        if (!empty($filters['partner_id'])) {
            $filters['partners.id'] = $filters['partner_id'];
            unset($filters['partner_id']);
        }

        $request->merge(['filters' => $filters]);

        $monthInput = DateHelper::parseMonthWithoutShift($filters['date']);
        $startDate = $monthInput->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $monthInput->copy()->endOfMonth()->format('Y-m-d');

        $query = $this->royaltyService->getPartnersWithStatsQuery($startDate, $endDate);

        $result = Pagination::paginate(
            $query,
            $request,
            [],
            ['name'],
            ['partners.id'],
        );

        $processedCollection = $this->royaltyService->transform(
            collect($result['list']),
            $monthInput,
        );

        $result['list'] = RoyaltyListResource::collection($processedCollection);

        return JsonResponse::Send($result);
    }
}
