<?php

namespace App\Http\Resources\DaData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bic'                   => data_get($this->resource, 'bic'),
            'inn'                   => data_get($this->resource, 'inn'),
            'kpp'                   => data_get($this->resource, 'kpp'),
            'okpo'                  => data_get($this->resource, 'okpo'),
            'correspondent_account' => data_get($this->resource, 'correspondent_account'),
        ];
    }
}
