<?php

declare(strict_types=1);

namespace App\Http\Resources\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsTotalCompareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'income_total'      => (float) $this->income_total,
            'income_goods'      => (float) $this->income_goods,
            'client_new'        => (int) $this->client_new,
            'client_return'     => (int) $this->client_return,
            'fullness_percent'  => (int) round($this->fullness_percent),
            'retention_percent' => (int) round($this->retention_percent),
            'average_sum'       => (int) $this->average_sum,
        ];
    }
}
