<?php

namespace App\Http\Controllers\Sheet;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Получить список сертификатов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Certificate::select(
            "price",
            "identifier",
            "partner",
        );

        return JsonResponse::Send(Pagination::paginate(
            $query,
            $request,
            ['identifier', 'partner'],
            ['id']
        ));
    }
}


