<?php

namespace App\Http\Controllers\PromoDialog;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromoDialog\PromoDialogCreateRequest;
use App\Http\Requests\PromoDialog\PromoDialogReadRequest;
use App\Http\Requests\PromoDialog\PromoDialogUpdateRequest;
use App\Http\Resources\PromoDialog\PromoDialogListResource;
use App\Http\Resources\PromoDialog\PromoDialogResource;
use App\Models\Promo\PromoDialog;
use App\Models\Promo\PromoDialogRead;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PromoDialogController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = PromoDialog::select(
            'id',
            'title',
            'template',
            'disabled',
            'created_at'
        );

        $result = Pagination::paginate(
            $query,
            $request,
            [
                'title',
            ],
            ['title'],
            ['disabled'],
        );

        $result['list'] = PromoDialogListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, PromoDialog $promoDialog): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => new PromoDialogResource($promoDialog),
        ]);
    }

    public function create(PromoDialogCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $promoDialog = PromoDialog::create($request->validated());

        return JsonResponse::Created();
    }

    public function update(PromoDialogUpdateRequest $request, PromoDialog $promoDialog): \Illuminate\Http\JsonResponse
    {
        $promoDialog->update($request->validated());

        return JsonResponse::Updated();
    }

    public function remove(Request $request, PromoDialog $promoDialog): \Illuminate\Http\JsonResponse
    {
        if ($promoDialog->template !== null) {
            return JsonResponse::UnprocessableEntity('Диалог с шаблоном нельзя удалить');
        }

        $promoDialog->delete();

        return JsonResponse::Removed();
    }

    /**
     * Отметить промо-диалог как прочитанный текущим пользователем.
     */
    public function read(PromoDialogReadRequest $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()->id;
        $dialogId = $request->promoDialogId();

        DB::transaction(fn (): PromoDialogRead => PromoDialogRead::query()->firstOrCreate(
            [
                'promo_dialog_id' => $dialogId,
                'user_id'         => $userId,
            ],
            [
                'read_at' => now(),
            ]
        )
        );

        return JsonResponse::Send([
            'success' => true,
        ]);
    }
}
