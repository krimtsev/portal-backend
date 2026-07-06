<?php

declare(strict_types=1);

namespace App\Http\Resources\Royalty;

use App\Helpers\NumberHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RoyaltyListResource extends JsonResource
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
            'vat_amount'       => $this['vat_amount'],
            'royalty_with_vat' => $this['royalty_with_vat'],
            'days_count'       => $this['days_count'],
            'start_at'         => $this['start_at'],
        ];
    }
}
