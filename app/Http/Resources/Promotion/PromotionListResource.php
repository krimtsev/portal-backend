<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'disabled'    => (bool) $this->disabled,
            'expires_at'  => $this->expires_at?->format('Y-m-d'),
            'is_expired'  => $this->expires_at?->isPast() ?? false,
            'user'        => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id'   => $this->user->id,
                    'name' => $this->user->name,
                ] : null;
            }),
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d'),
        ];
    }
}
