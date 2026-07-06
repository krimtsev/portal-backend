<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sheet;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Certificate\Certificate;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

final class CertificateController extends Controller
{
    /**
     * Получить список сертификатов
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Certificate::select(
            'price',
            'identifier',
            'partner',
        );

        return JsonResponse::Send(Pagination::paginate(
            $query,
            $request,
            ['identifier', 'partner'],
            ['id']
        ));
    }
}
