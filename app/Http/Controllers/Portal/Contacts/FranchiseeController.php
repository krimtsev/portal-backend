<?php


namespace App\Http\Controllers\Portal\Contacts;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\Partner\Partner;
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
        $query = Partner::activeWhere(['id', 'name', 'telnums']);

        return JsonResponse::Send(Pagination::paginate(
            $query,
            $request,
            ['name'],
            ['id']
        ));
    }
}


