<?php

namespace App\Http\Resources\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'income_total'      => (float) $this->income_total,
            'income_goods'      => (float) $this->income_goods,
            'income_services'   => (float) $this->income_services,
            'fullness_percent'  => (int) round($this->fullness_percent),
            'record_completed'  => (int) $this->record_completed,
            'record_pending'    => (int) $this->record_pending,
            'record_canceled'   => (int) $this->record_canceled,
            'record_total'      => (int) $this->record_total,
            'client_new'        => (int) $this->client_new,
            'client_return'     => (int) $this->client_return,
            'client_active'     => (int) $this->client_active,
            'client_lost'       => (int) $this->client_lost,
            'client_total'      => (int) $this->client_total,
        ];
    }
}
