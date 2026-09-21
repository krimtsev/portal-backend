<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\Partner\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Partner
 */
final class PayrollPartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'partner_id'         => $this->id,
            'partner_name'       => $this->name,
            'current_value'      => round((float) ($this->current_value ?? 0), 2),
            'past_value'         => round((float) ($this->past_value ?? 0), 2),
            'percent_difference' => (float) ($this->percent_difference ?? 0),
        ];
    }
}
