<?php

namespace App\Http\Controllers\Royalty;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\Royalty\RoyaltyListResource;
use App\Models\Partner\Partner;
use App\Models\Yclient\YcCompanyDailyStat;
use App\Responses\JsonResponse;
use App\Services\Royalty\RoyaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class _RoyaltyController extends Controller
{
    protected RoyaltyService $royaltyService;

    public function __construct(RoyaltyService $royaltyService)
    {
        $this->royaltyService = $royaltyService;
    }

    public function list(Request $request)
    {
        $filters = $request->input('filters', []);

        // Определяем границы отчетного месяца (по умолчанию текущий)
        $monthInput = isset($filters['date'])
            ? Carbon::parse($filters['date'])->format('Y-m')
            : Carbon::now()->format('Y-m');

        $startDate = Carbon::parse($monthInput)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($monthInput)->endOfMonth()->format('Y-m-d');

        // Строим запрос строго от действующих партнеров
        $query = Partner::withActiveYclients()
            ->select('id', 'name', 'opened_at') // Берем только нужное
            ->addSelect([
                'income_total' => YcCompanyDailyStat::selectRaw('COALESCE(SUM(income_total), 0)')
                    ->whereColumn('company_id', 'partners.yclients_id')
                    ->whereBetween('date', [$startDate, $endDate]),
            ]);

        // Пагинация по списку партнеров
        $result = Pagination::paginate(
            $query,
            $request,
            [],
            ['name'],
            ['partner_id'],
        );

        // Расчет коммерческих условий на уровне сервиса
        $processedCollection = $this->royaltyService->prepareMonthlyRoyaltyList(
            collect($result['list']),
            $monthInput
        );

        $result['list'] = RoyaltyListResource::collection($processedCollection);

        return JsonResponse::Send($result);
    }
}
