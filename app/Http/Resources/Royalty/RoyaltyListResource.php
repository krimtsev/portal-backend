<?php

namespace App\Http\Resources\Royalty;

use App\Helpers\NumberHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoyaltyListResource extends JsonResource
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
            'royalty_amount'   => NumberHelper::money($this['royalty_amount']),
            'vat_percent'      => $this['vat_percent'],
            'vat_amount'       => NumberHelper::money($this['vat_amount']),
            'royalty_with_vat' => NumberHelper::money($this['royalty_with_vat']),
            'days_count'       => $this['days_count'],
            'opened_at'        => $this['opened_at'],
        ];
    }
}
