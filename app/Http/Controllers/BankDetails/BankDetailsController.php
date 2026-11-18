<?php

declare(strict_types=1);

namespace App\Http\Controllers\BankDetails;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankDetail\BankDetailCreateRequest;
use App\Http\Requests\BankDetail\BankDetailListRequest;
use App\Http\Requests\BankDetail\BankDetailUpdateRequest;
use App\Http\Resources\BankDetail\BankDetailExportResource;
use App\Http\Resources\BankDetail\BankDetailListResource;
use App\Http\Resources\BankDetail\BankDetailResource;
use App\Http\Resources\BankDetail\BankDetailUserOptionResource;
use App\Models\BankDetail\BankDetail;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

final class BankDetailsController extends Controller
{
    private function isDashboard(): bool
    {
        return (bool) request()->attributes->get('is_dashboard', false);
    }

    public function list(BankDetailListRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = BankDetail::query()
            ->with([
                'user:id,name,login',
                'partner:id,name',
            ])
            ->select(
                'id',
                'user_id',
                'partner_id',
                'entrepreneur_name',
                'inn',
                'checking_account',
                'bank_name',
                'bik',
                'correspondent_account',
                'created_at',
                'updated_at',
            );

        $result = Pagination::paginate(
            $query,
            $request,
            [
                'entrepreneur_name',
                'inn',
                'bank_name',
                'bik',
            ],
            [
                'entrepreneur_name',
                'created_at',
            ],
            [
                'partner_id',
                'user_id',
            ],
        );

        $result['list'] = BankDetailListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(BankDetail $bankDetail): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => new BankDetailResource(
                $bankDetail->load([
                    'partner:id,name',
                    'user:id,name,login',
                ])
            ),
        ]);
    }

    public function create(BankDetailCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $attributes = $request->validated();
        $attributes['created_by'] = $user->id;

        /**
         * Реквизиты вне панели
         * автоматически регистрируются за пользователем который создает
         */
        if (!$this->isDashboard()) {
            $attributes['user_id'] = $user->id;
        }

        BankDetail::create($attributes);

        return JsonResponse::Created();
    }

    public function update(BankDetailUpdateRequest $request, BankDetail $bankDetail): \Illuminate\Http\JsonResponse
    {
        $attributes = $request->validated();

        /**
         * Реквизиты вне панели
         * - недоступно изменение привязки к пользователю
         * - назначение партнера доступно только из своего списка
         */
        if (!$this->isDashboard()) {
            $user = $request->user();

            if ((int) $bankDetail->user_id !== (int) $user->id) {
                return JsonResponse::Forbidden('You can only update your own bank details.');
            }

            unset($attributes['user_id']);

            if (array_key_exists('partner_id', $attributes) && $attributes['partner_id'] !== null) {
                $allowedPartnerIds = $user->allowedPartnerIds();

                if (!in_array((int) $attributes['partner_id'], $allowedPartnerIds, true)) {
                    return JsonResponse::Forbidden('The selected partner is not available to you.');
                }
            }
        }

        $bankDetail->update($attributes);

        return JsonResponse::Updated();
    }

    public function remove(Request $request, BankDetail $bankDetail): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        /**
         * Реквизиты вне панели
         * разрешается удалять только назначенные на текущего пользователя
         */
        if (!$this->isDashboard() && (int) $bankDetail->user_id !== (int) $user->id) {
            return JsonResponse::Forbidden('You can only delete your own bank details.');
        }

        $bankDetail->delete();

        return JsonResponse::Removed();
    }

    public function export(BankDetailListRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = BankDetail::query()
            ->with([
                'user:id,name,login',
                'partner:id,name',
            ])
            ->select(
                'id',
                'user_id',
                'partner_id',
                'entrepreneur_name',
                'inn',
                'checking_account',
                'bank_name',
                'bik',
                'correspondent_account',
                'created_at',
                'updated_at',
            );

        return JsonResponse::Send([
            'list' => BankDetailExportResource::collection($query->get()),
        ]);
    }

    /**
     * Полный список реквизитов (без пагинации).
     */
    public function options(): \Illuminate\Http\JsonResponse
    {
        $list = BankDetail::query()
            ->select(
                'id',
                'entrepreneur_name',
                'inn',
                'checking_account',
                'bank_name',
                'bik',
                'correspondent_account',
            )
            ->get();

        return JsonResponse::Send([
            'list' => $list,
        ]);
    }

    /**
     * Список реквизитов, принадлежащих текущему пользователю.
     */
    public function userOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        $list = BankDetail::query()
            ->with('partner:id,name')
            ->where('user_id', $request->user()->id)
            ->select(
                'id',
                'partner_id',
                'entrepreneur_name',
                'inn',
                'checking_account',
                'bank_name',
                'bik',
                'correspondent_account',
            )
            ->get();

        return JsonResponse::Send([
            'list' => BankDetailUserOptionResource::collection($list),
        ]);
    }
}
