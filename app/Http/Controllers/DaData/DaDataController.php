<?php

namespace App\Http\Controllers\DaData;

use App\Http\Controllers\Controller;
use App\Integrations\DaData\DaDataApi;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

class DaDataController extends Controller
{
    public function __construct(
        private readonly DaDataApi $dadata,
    ) {}

    public function bankSuggest(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = (string) $request->input('query', 'сбербанк');
        $count = (int) $request->input('count', 5);

        $data = $this->dadata->bank()->suggest($query, $count);

        return JsonResponse::Send([
            'data' => $data,
        ]);
    }
}
