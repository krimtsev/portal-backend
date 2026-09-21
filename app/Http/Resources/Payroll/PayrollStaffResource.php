<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PayrollStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->staff_id,
            'avatar'           => $this->avatar,
            'name'             => $this->name,
            'specialization'   => $this->specialization,
            'sum'              => $this->total_sum,
            'daily_sums'       => $this->daily_sums,
            'partner_turnover' => $this->partner_turnover,
        ];
    }
}
