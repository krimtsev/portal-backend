<?php

namespace App\Http\Controllers\PromoDialog;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoDialog\PromoDialogReadRequest;
use App\Models\Promo\PromoDialogRead;
use App\Responses\JsonResponse;
use Illuminate\Support\Facades\DB;

final class PromoDialogController extends Controller
{
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
