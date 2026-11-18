<?php

declare(strict_types=1);

namespace App\Http\Resources\Cloud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CloudFileListItemResource extends JsonResource
{
    private function dateValue(): ?string
    {
        $date = $this->updated_at ?? $this->created_at;

        return $date?->format('Y-m-d H:i:s');
    }

    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'name'             => $this->name,
            'ext'              => $this->ext,
            'cloud_folders_id' => $this->cloud_folders_id,
            'size'             => $this->size,
            'date'             => $this->dateValue(),
        ];
    }
}
