<?php

namespace App\Http\Controllers\PromoDialog;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromoDialog\PromoDialogCreateRequest;
use App\Http\Requests\PromoDialog\PromoDialogReadRequest;
use App\Http\Requests\PromoDialog\PromoDialogUpdateRequest;
use App\Http\Resources\PromoDialog\PromoDialogExportResource;
use App\Http\Resources\PromoDialog\PromoDialogListResource;
use App\Http\Resources\PromoDialog\PromoDialogResource;
use App\Models\Promo\PromoDialog;
use App\Models\Promo\PromoDialogRead;
use App\Models\User\User;
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

    public function export(Request $request, PromoDialog $promoDialog): \Illuminate\Http\JsonResponse
    {
        $rows = User::query()
            ->select([
                'users.id',
                'users.login',
                'users.name',
                'users.disabled',
                'users.partner_id',
            ])
            ->with([
                'partner' => fn ($q) => $q->select('id', 'name', 'organization')->withTrashed(),
            ])
            ->leftJoin('promo_dialog_reads', function ($join) use ($promoDialog): void {
                $join->on('promo_dialog_reads.user_id', '=', 'users.id')
                    ->where('promo_dialog_reads.promo_dialog_id', '=', $promoDialog->id);
            })
            ->addSelect('promo_dialog_reads.read_at as read_at')
            ->orderByRaw('promo_dialog_reads.read_at IS NULL')
            ->orderBy('promo_dialog_reads.read_at')
            ->orderBy('users.login')
            ->get();

        return JsonResponse::Send([
            'dialog' => [
                'id'    => $promoDialog->id,
                'title' => $promoDialog->title,
            ],
            'list' => PromoDialogExportResource::collection($rows),
        ]);
    }
}
