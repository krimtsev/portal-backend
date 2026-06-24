<?php

namespace App\Http\Controllers\Royalty;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Royalty\RoyaltyListRequest;
use App\Http\Resources\Royalty\RoyaltyListResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Royalty\RoyaltyService;
use Carbon\Carbon;

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

        $monthInput = Carbon::parse($filters['date']);
        $startDate = Carbon::parse($monthInput)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($monthInput)->endOfMonth()->format('Y-m-d');

        $query = Partner::withRoyalty()
            ->select(
                'partners.id',
                'partners.name',
                'partners.yclients_id',
                'partners.start_at',
                'partners.opened_at',
            )
            ->leftJoin('yc_company_daily_stats as stats', function ($join) use ($startDate, $endDate) {
                $join->on('stats.company_id', '=', 'partners.yclients_id')
                    ->whereBetween('stats.date', [$startDate, $endDate]);
            })
            ->selectRaw('COALESCE(SUM(stats.income_total), 0) as income_total')
            ->selectRaw('COUNT(DISTINCT stats.date) as days_count')
            ->groupBy(
                'partners.id',
                'partners.name',
                'partners.yclients_id',
                'partners.start_at'
            );

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
