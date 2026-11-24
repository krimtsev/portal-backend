<?php

declare(strict_types=1);

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PromotionExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'disabled'    => $this->disabled,
            'expires_at'  => $this->expires_at?->format('Y-m-d'),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'user'        => $this->whenLoaded('user', function () {
                return $this->user->name ?? $this->user->login;
            }),
        ];
    }
}
