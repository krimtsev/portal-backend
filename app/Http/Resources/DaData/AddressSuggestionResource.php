<?php

namespace App\Http\Resources\DaData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            //TODO: добавить типы
        ];
    }
}
