<?php

declare(strict_types=1);

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
use App\Services\Partners\PartnerService;
use Illuminate\Http\Request;

final class PartnerController extends Controller
{
    public function __construct(
        private readonly PartnerService $partnerService,
    ) {}

    /**
     * Получение списка партнеров доступных пользователю
     * Учитываем partner_id и partner_groups
     */
    public function getUserPartners(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->partnerService->getUserPartnersData($request->user());

        return JsonResponse::Send($result);
    }

    public function options(): \Illuminate\Http\JsonResponse
    {
        $list = Partner::activeWhere(['id', 'name'])
            ->orderBy('name')
            ->get();

        return JsonResponse::Send([
            'list' => $list,
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
        );

        $result = Pagination::paginate(
            $query,
            $request,
            [
                'name',
                'organization',
                'inn',
                'ogrnip',
                'yclients_id',
                'mango_telnum',
                'contract_number',
                'address',
                'telnums.number',
            ],
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
        $this->partnerService->create($request->validated());

        return JsonResponse::Created();
    }

    public function update(PartnerUpdateRequest $request, Partner $partner): \Illuminate\Http\JsonResponse
    {
        $this->partnerService->update($partner, $request->validated());

        return JsonResponse::Updated();
    }

    public function export(): array
    {
        $partners = Partner::query()
            ->select(
                'id',
                'organization',
                'name',
                'inn',
                'ogrnip',
                'contract_number',
                'address',
                'email',
                'yclients_id',
                'mango_telnum',
                'disabled',
                'start_at',
                'opened_at'
            )
            ->orderBy('name')
            ->get();

        return PartnerExportResource::collection($partners)->resolve();
    }
}
