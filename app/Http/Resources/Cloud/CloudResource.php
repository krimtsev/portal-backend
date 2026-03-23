<?php
namespace App\Http\Resources\Cloud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CloudResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'category_id' => $this->category_id,

            'files'       => $this->whenLoaded('files', function() {
                return $this->files->map(function($file) {
                    return [
                        'id'        => $file->id,
                        'title'     => $file->title,
                        'ext'       => $file->ext,
                        'downloads' => $file->downloads,
                        'name'      => $file->name,
                    ];
                });
            }),
        ];
    }
}
