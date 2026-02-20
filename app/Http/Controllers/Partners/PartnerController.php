<?php

namespace App\Http\Controllers\Partners;

use App\Helpers\Pagination\Pagination;
use App\Http\Resources\Partner\PartnerListResource;
use App\Models\Partner\Partner;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Получение списка партнеров доступных пользователю
     * Учиитываем partner_id и partner_groups
     */
    public function getUserPartners(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $partnerId = $user->partner_id;

        if (!$partnerId) {
            return JsonResponse::Send([
                'partner_id' => null,
                'partners' => []
            ]);
        }

        $partner = Partner::with(['group.partners' => function ($query) {
            $query->orderBy('name');
        }])->findOrFail($partnerId);

        if ($partner->group) {
            $partners = $partner->group->partners->map(function ($partner) {
                return [
                    'partner_id' => $partner->id,
                    'name' => $partner->name
                ];
            });
        } else {
            $partners = collect([
                [
                    'partner_id' => $partner->id,
                    'name' => $partner->name,
                ]
            ]);
        }

        return JsonResponse::Send([
            'partner_id' => $partnerId,
            'partners' => $partners,
        ]);
    }

    public function shortList(): \Illuminate\Http\JsonResponse
    {
        $partners = Partner::activeWhere(['id', 'name'])
            ->orderBy('name')
            ->get();

        return JsonResponse::Send([
            'list' => $partners
        ]);
    }

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Partner::select(
            'id',
            'name',
            'inn',
            'ogrnip',
            'organization',
            'yclients_id',
            'mango_telnum',
            'contract_number',
            'start_at',
            'disabled'
        )->orderBy('name', 'asc');

        $result = Pagination::paginate(
            $query,
            $request,
            ['name'],
            ['name'],
            [],
        );

        $result['list'] = PartnerListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }
}
