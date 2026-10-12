<?php

namespace App\Http\Controllers\Telnums;

use App\Helpers\Pagination\Pagination;
use App\Http\Requests\Telnums\TelnumsIncomingListRequest;
use App\Http\Requests\Telnums\TelnumsIncomingPartnerDayRequest;
use App\Http\Resources\Telnums\TelnumsIncomingListResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Telnums\TelnumsIncomingService;
use Illuminate\Http\Request;

class TelnumsIncomingController
{
    private function isDashboard(): bool
    {
        return (bool) request()->attributes->get('is_dashboard', false);
    }

    /**
     * Используется в панели для отображении таблицы по всем партнерам.
     */
    public function list(
        TelnumsIncomingListRequest $request,
        TelnumsIncomingService $service
    )
    {
        $filters = $request->filters();

        $date = $filters['date'];
        unset($filters['date']);

        if (!empty($filters['partner_id'])) {
            $filters['partners.id'] = $filters['partner_id'];
            unset($filters['partner_id']);
        }

        $request->merge(['filters' => $filters]);

        $query = $service->getIncomingStatsQuery($date);

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
    )
    {
        $partner = Partner::query()
            ->whereNotNull('mango_telnum')
            ->findOrFail($partnerId);

        return JsonResponse::Send([
            'data' => $service->getPartnerDayHourlyStats($partner, $request->validated('date'))
        ]);
    }
}
