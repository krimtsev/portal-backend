<?php

declare(strict_types=1);

namespace App\Http\Resources\Cloud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CloudFileResource extends JsonResource
{
    /**
     * Дата обновления, иначе — дата создания (Y-m-d H:i:s)
     */
    private function dateValue(): ?string
    {
        $date = $this->updated_at ?? $this->created_at;

        return $date?->format('Y-m-d H:i:s');
    }

    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'ext'       => $this->ext,
            'downloads' => $this->downloads,
            'name'      => $this->name,
            'date'      => $this->dateValue(),
            'size'      => $this->size,
        ];
    }
}
