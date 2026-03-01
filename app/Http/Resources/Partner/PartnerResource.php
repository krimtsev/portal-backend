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
            'start_at'        => $this->start_at?->format('Y-m-d'),
            'group_id'        => $this->group_id,
            'disabled'        => (bool) $this->disabled,
            'telnums'         => $this->whenLoaded('telnums', function() {
                return $this->telnums->map(function($telnum) {
                    return [
                        'id'         => $telnum->id,
                        'name'       => $telnum->name,
                        'number'     => $telnum->number,
                    ];
                });
            }),
        ];
    }
}
