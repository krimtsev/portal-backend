<?php

namespace App\Http\Resources\DaData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = data_get($this->resource, 'data', []);

        return [
            "country" => data_get($data, 'country'),
            "region"  => data_get($data, 'region'),
            "city"    => data_get($data, 'city'),
        ];
    }
}
