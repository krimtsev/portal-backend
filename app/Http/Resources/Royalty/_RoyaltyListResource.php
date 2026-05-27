<?php

namespace App\Http\Resources\Royalty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _RoyaltyListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'partner_id'       => $this['partner_id'],
            'partner_name'     => $this['partner_name'],
            'gross_revenue'    => $this['gross_revenue'],
            'royalty_percent'  => $this['royalty_percent'],
            'royalty_amount'   => $this['royalty_amount'],
            'vat_percent'      => $this['vat_percent'],
            'vat_amount'       => $this['vat_amount'],
            'royalty_with_vat' => $this['royalty_with_vat'],
            'opened_at'        => $this['opened_at'],
        ];
    }
}
