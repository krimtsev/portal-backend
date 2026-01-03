<?php

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'            => $this->name,
            'address'         => $this->address,
            'inn'             => $this->inn,
            'ogrnip'          => $this->ogrnip,
            'organization'    => $this->organization,
            'yclients_id'     => $this->yclients_id,
            'mango_telnum'    => $this->mango_telnum,
            'contract_number' => $this->contract_number,
            'email'           => $this->email,
        ];
    }
}
