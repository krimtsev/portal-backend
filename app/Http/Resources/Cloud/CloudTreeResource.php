<?php

namespace App\Http\Resources\Cloud;

use Illuminate\Http\Resources\Json\JsonResource;

enum CloudType: string
{
    case FOLDER = 'folder';
    case FILE = 'file';
}

class CloudTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return isset($this->slug)
            ? $this->folderToArray()
            : $this->fileToArray();
    }

    /**
     * Структура для папки
     */
    private function folderToArray(): array
    {
        return [
            'key'      => "folder-{$this->id}",
            'label'    => $this->name,
            'data'     => [
                'id'        => $this->id,
                'name'      => $this->name,
                'type'      => CloudType::FOLDER->value,
                'ext'       => null,
                'downloads' => null,
                'slug'      => $this->slug,
                'path'      => $this->path_array,
            ],
            'children' => $this->getMergedChildren(),
            'leaf'     => false,
        ];
    }

    /**
     * Структура для файла
     */
    private function fileToArray(): array
    {
        return [
            'key'      => "file-{$this->id}",
            'label'    => $this->title,
            'data'     => [
                'id'        => $this->id,
                'name'      => $this->title,
                'type'      => CloudType::FILE->value,
                'ext'       => $this->ext,
                'downloads' => $this->downloads,
                'slug'      => null,
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
