<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PromotionPublicListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description,
            'created_at'  => $this->created_at?->format('Y-m-d'),
            'image'       => $this->image
                ? Storage::disk('promotions')->url($this->image)
                : null,
        ];
    }
}
