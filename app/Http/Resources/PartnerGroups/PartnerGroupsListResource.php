<?php

declare(strict_types=1);

namespace App\Http\Resources\PartnerGroups;

use Illuminate\Http\Resources\Json\JsonResource;

final class PartnerGroupsListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'title'    => (string) $this->title,
            'total'    => (int) $this->partners_count,
            'partners' => $this->partners->pluck('name')->toArray(),
        ];
    }
}
