<?php

namespace App\Http\Controllers\Partners;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerCreateRequest;
use App\Http\Requests\Partner\PartnerUpdateRequest;
use App\Http\Resources\Partner\PartnerExportResource;
use App\Http\Resources\Partner\PartnerListResource;
use App\Http\Resources\Partner\PartnerResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function options(): \Illuminate\Http\JsonResponse
    {
        $list = Partner::activeWhere(['id', 'name'])
            ->orderBy('name')
            ->get();

        return JsonResponse::Send([
            'list' => $list
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
            ['disabled'],
        );

        $result['list'] = PartnerListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, Partner $partner): \Illuminate\Http\JsonResponse
    {
        $partner->load('telnums');

        return JsonResponse::Send([
            'data' => new PartnerResource($partner),
        ]);
    }

    public function create(PartnerCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $partner = Partner::create($data);

            if (!empty($data['telnums'])) {
                $partner->telnums()->createMany($data['telnums']);
            }
        });

        return JsonResponse::Created();
    }

    public function update(PartnerUpdateRequest $request, Partner $partner): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $partner) {
            $partner->update($data);

            if (isset($data['telnums'])) {
                $telnumsData = collect($data['telnums']);

                $idsToKeep = $telnumsData->pluck('id')->filter()->toArray();
                $partner->telnums()->whereNotIn('id', $idsToKeep)->delete();

                foreach ($telnumsData as $telnumItem) {
                    $partner->telnums()->updateOrCreate(
                        ['id' => $telnumItem['id'] ?? null],
                        [
                            'name'   => $telnumItem['name'] ?? null,
                            'number' => $telnumItem['number']
                        ]
                    );
                }
            }
        });

        return JsonResponse::Updated();
    }

    public function export(): array
    {
        $users = Partner::orderBy('name')
            ->get();

        return PartnerExportResource::collection($users)->resolve();
    }
}
