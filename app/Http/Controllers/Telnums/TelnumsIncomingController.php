<?php

namespace App\Http\Controllers\Telnums;

use App\Helpers\Pagination\Pagination;
use App\Http\Requests\Telnums\TelnumsIncomingListRequest;
use App\Http\Requests\Telnums\TelnumsIncomingPartnerDayRequest;
use App\Http\Resources\Telnums\TelnumsIncomingListResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Telnums\TelnumsIncomingService;

class TelnumsIncomingController
{
    /**
     * Используется в панели для отображении таблицы по всем партнерам.
     */
    public function list(
        TelnumsIncomingListRequest $request,
        TelnumsIncomingService $service
    ) {
        $filters = $request->filters();

        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];

        unset(
            $filters['date_from'],
            $filters['date_to']
        );

        if (!empty($filters['partner_id'])) {
            $filters['partners.id'] = $filters['partner_id'];
            unset($filters['partner_id']);
        }

        $request->merge(['filters' => $filters]);

        $query = $service->getIncomingStatsQuery($dateFrom, $dateTo);

        $result = Pagination::paginate(
            $query,
            $request,
            [],
            ['name', 'success_count', 'failed_count', 'failed_percent', 'total_count'],
            ['partners.id'],
            tieBreaker: 'partners.id'
        );

        $result['list'] = TelnumsIncomingListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function partnerDay(
        TelnumsIncomingPartnerDayRequest $request,
        int $partnerId,
        TelnumsIncomingService $service
    ) {
        $partner = Partner::query()
            ->whereNotNull('mango_telnum')
            ->findOrFail($partnerId);

        return JsonResponse::Send([
            'data' => $service->getPartnerPeriodHourlyStats(
                $partner,
                $request->validated('date_from'),
                $request->validated('date_to'),
            ),
        ]);
    }
}
