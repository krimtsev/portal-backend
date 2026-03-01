<?php

namespace App\Http\Controllers\Partners;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerGroupCreateRequest;
use App\Http\Requests\Partner\PartnerGroupUpdateRequest;
use App\Http\Resources\PartnerGroups\PartnerGroupsListResource;
use App\Http\Responses\JsonResponse;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerGroupController extends Controller
{
    public function options(): \Illuminate\Http\JsonResponse
    {
        $list = PartnerGroup::select(['id', 'title'])
            ->orderBy('title')
            ->get();

        return JsonResponse::Send([
            'list' => $list
        ]);
    }

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query= PartnerGroup::select(['id', 'title'])
            ->withCount('partners')
            ->orderBy('title');

        $result = Pagination::paginate(
            $query,
            $request,
            ['title'],
            ['title'],
            [],
        );

        $result['list'] = PartnerGroupsListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, PartnerGroup $partnerGroup): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => [
                'title'    => $partnerGroup->title,
                'partners' => $partnerGroup->partners()->pluck('id'),
            ],
        ]);
    }

    public function create(PartnerGroupCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $group = PartnerGroup::create([
                'title' => $data['title']
            ]);

            if (!empty($data['partners'])) {
                Partner::whereIn('id', $data['partners'])
                    ->update(['group_id' => $group->id]);
            }
        });

        return JsonResponse::Created();
    }

    public function update(PartnerGroupUpdateRequest $request, PartnerGroup $partnerGroup): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $partnerGroup) {
            $partnerGroup->update([
                'title' => $data['title']
            ]);

            $newPartnerIds = $data['partners'] ?? [];

            // Отвязываем партнеров, которые больше не входят в группу
            Partner::where('group_id', $partnerGroup->id)
                ->whereNotIn('id', $newPartnerIds)
                ->update(['group_id' => null]);

            // Привязываем новых партнеров
            if (!empty($newPartnerIds)) {
                Partner::whereIn('id', $newPartnerIds)
                    ->update(['group_id' => $partnerGroup->id]);
            }
        });

        return JsonResponse::Updated();
    }
}
