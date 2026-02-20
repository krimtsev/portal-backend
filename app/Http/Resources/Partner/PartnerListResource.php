<?php

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => (string) $this->name,
            'address'         => (string) $this->address,
            'inn'             => (string) $this->inn,
            'ogrnip'          => (string) $this->ogrnip,
            'organization'    => (string) $this->organization,
            'yclients_id'     => (string) $this->yclients_id,
            'mango_telnum'    => (string) $this->mango_telnum,
            'contract_number' => (string) $this->contract_number,
            'disabled'        => (bool) $this->disabled,
            'start_at'        => $this->start_at?->format('Y-m-d'),
        ];
    }
}
