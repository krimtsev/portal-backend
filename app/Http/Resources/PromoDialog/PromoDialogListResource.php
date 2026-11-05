<?php

declare(strict_types=1);

namespace App\Http\Resources\PromoDialog;

use Illuminate\Http\Resources\Json\JsonResource;

final class PromoDialogListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'template'    => $this->template,
            'disabled'    => (bool) $this->disabled,
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
