<?php

declare(strict_types=1);

namespace App\Http\Resources\Cloud;

use App\Enums\Cloud\CloudType;
use Illuminate\Http\Resources\Json\JsonResource;

final class CloudTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return isset($this->slug)
            ? $this->folderToArray()
            : $this->fileToArray();
    }

    /**
     * Дата обновления, иначе — дата создания (Y-m-d H:i:s)
     */
    private function dateValue(): ?string
    {
        $date = $this->updated_at ?? $this->created_at;

        return $date?->format('Y-m-d H:i:s');
    }

    private function folderToArray(): array
    {
        return [
            'key'   => "folder-{$this->id}",
            'label' => $this->name,
            'data'  => [
                'id'        => $this->id,
                'name'      => $this->name,
                'type'      => CloudType::FOLDER->value,
                'ext'       => null,
                'downloads' => null,
                'slug'      => $this->slug,
                'path'      => $this->path_array,
                'date'      => $this->dateValue(),
                'author'    => $this->author,
            ],
            'children' => $this->getMergedChildren(),
            'leaf'     => false,
        ];
    }

    private function fileToArray(): array
    {
        return [
            'key'   => "file-{$this->id}",
            'label' => $this->title,
            'data'  => [
                'id'        => $this->id,
                'name'      => $this->title,
                'type'      => CloudType::FILE->value,
                'ext'       => $this->ext,
                'downloads' => $this->downloads,
                'slug'      => null,
                'date'      => $this->dateValue(),
                'size'      => $this->size,
                'author'    => $this->author,
            ],
            'children' => null,
            'leaf'     => true,
        ];
    }

    private function getMergedChildren(): array
    {
        $folders = $this->relationLoaded('children') ? $this->children : collect();
        $files = $this->relationLoaded('files') ? $this->files : collect();

        if ($folders->isEmpty() && $files->isEmpty()) {
            return [];
        }

        return array_merge(
            CloudTreeResource::collection($folders)->toArray(request()),
            CloudTreeResource::collection($files)->toArray(request())
        );
    }
}
