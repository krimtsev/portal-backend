<?php

namespace App\Http\Resources\DaData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = data_get($this->resource, 'data', []);

        return [
            "name"                  => data_get($data, 'name.payment'),
            'bic'                   => data_get($data, 'bic'),
            'inn'                   => data_get($data, 'inn'),
            'kpp'                   => data_get($data, 'kpp'),
            'okpo'                  => data_get($data, 'okpo'),
            'correspondent_account' => data_get($data, 'correspondent_account'),
        ];
    }
}
