<?php

declare(strict_types=1);

namespace App\Http\Resources\Royalty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RoyaltyRecordsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'staff' => $this->resource['staff'],
            'data'  => $this->resource['data'],
        ];
    }
}
