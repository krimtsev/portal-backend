<?php

namespace App\Http\Controllers\DaData;

use App\Enums\DaData\DaDataStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DaData\DaDataBaseRequest;
use App\Http\Resources\DaData\AddressResource;
use App\Http\Resources\DaData\BankResource;
use App\Http\Resources\DaData\PartyResource;
use App\Integrations\DaData\DaDataApi;
use App\Responses\JsonResponse;

class DaDataController extends Controller
{
    public function __construct(
        private readonly DaDataApi $dadata,
    ) {}

    /**
     * Банки - Автодополнение при вводе («подсказки»)
     */
    public function bankSuggest(DaDataBaseRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');
        $count = $request->validated('count', 5);
        $statuses = $request->validated('status', [DaDataStatus::ACTIVE->value]);

        $payload = ['status' => $statuses];

        $items = $this->dadata->bank()->suggest($query, $count, $payload);

        return JsonResponse::Send([
            'data' => BankResource::collection($items),
        ]);
    }

    /**
     * Банки - Точное совпадение по БИК, SWIFT, ИНН, рег. номеру
     */
    public function bankFindById(DaDataBaseRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');

        $items = $this->dadata->bank()->findById($query);

        return JsonResponse::Send([
            'data' => BankResource::collection($items),
        ]);
    }

    public function addressSuggest(DaDataBaseRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');
        $count = $request->validated('count', 5);

        $items = $this->dadata->party()->suggest($query, $count);

        return JsonResponse::Send([
            'data' => AddressResource::collection($items),
        ]);
    }

    /**
     * Компании - Автодополнение при вводе («подсказки»)
     */
    public function partySuggest(DaDataBaseRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');
        $count = $request->validated('count', 5);
        $statuses = $request->validated('status', [DaDataStatus::ACTIVE->value]);

        $payload = ['status' => $statuses];

        $items = $this->dadata->party()->suggest($query, $count, $payload);

        return JsonResponse::Send([
            'data' => PartyResource::collection($items),
        ]);
    }

    /**
     * Компании - Организация по ИНН, КПП, ОГРН
     */
    public function partyFindById(DaDataBaseRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->validated('query');
        $count = $request->validated('count', 5);
        $statuses = $request->validated('status', [DaDataStatus::ACTIVE->value]);

        $payload = ['status' => $statuses];

        $items = $this->dadata->party()->findById($query, $count, $payload);

        return JsonResponse::Send([
            'data' => PartyResource::collection($items),
        ]);
    }
}
