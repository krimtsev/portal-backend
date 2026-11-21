<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'disabled'   => (bool) $this->disabled,
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'image'      => $this->image
                ? Storage::disk('promotions')->url($this->image)
                : null,
        ];
    }
}
