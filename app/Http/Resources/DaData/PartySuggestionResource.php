<?php

namespace App\Http\Resources\DaData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartySuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = data_get($this->resource, 'data', []);

        return [
            'name'    => data_get($data, 'name.short_with_opf'),
            'inn'     => data_get($data, 'inn'),
            'ogrn'    => data_get($data, 'ogrn'),
            'okpo'    => data_get($data, 'okpo'),
            'address' => data_get($data, 'address.data.source'),
            'emails'  => data_get($data, 'emails'),
        ];
    }
}
