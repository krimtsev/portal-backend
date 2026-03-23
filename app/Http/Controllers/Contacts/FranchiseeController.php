<?php


namespace App\Http\Controllers\Contacts;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

class FranchiseeController extends Controller
{
    /**
     * Получить список контактов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Partner::with('telnums')
                    ->activeWhere(['id', 'name']);

        return JsonResponse::Send(Pagination::paginate(
            $query,
            $request,
            ['name', 'telnums.number', 'telnums.name'],
            ['name', 'id']
        ));
    }
}


