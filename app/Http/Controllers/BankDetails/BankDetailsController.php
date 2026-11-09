<?php

declare(strict_types=1);

namespace App\Http\Controllers\BankDetails;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankDetail\BankDetailCreateRequest;
use App\Http\Requests\BankDetail\BankDetailUpdateRequest;
use App\Http\Resources\BankDetail\BankDetailListResource;
use App\Http\Resources\BankDetail\BankDetailResource;
use App\Models\BankDetail\BankDetail;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

final class BankDetailsController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
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
            ['partner_id'],
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
        BankDetail::create($request->validated());

        return JsonResponse::Created();
    }

    public function update(BankDetailUpdateRequest $request, BankDetail $bankDetail): \Illuminate\Http\JsonResponse
    {
        $bankDetail->update($request->validated());

        return JsonResponse::Updated();
    }

    public function remove(Request $request, BankDetail $bankDetail): \Illuminate\Http\JsonResponse
    {
        $bankDetail->delete();

        return JsonResponse::Removed();
    }

    public function export(Request $request): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([

        ]);
    }
}
