<?php
namespace App\Http\Resources\Partner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PartnerExportResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id'              => $this->id,
            'organization'    => $this->organization,
            'name'            => $this->name,
            'inn'             => $this->inn,
            'ogrnip'          => $this->ogrnip,
            'contract_number' => $this->contract_number,
            'address'         => $this->address,
            'disabled'        => (bool)$this->disabled,
            'start_at'        => $this->start_at?->format('Y-m-d'),
        ];
    }

}
