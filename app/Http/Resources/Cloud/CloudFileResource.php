<?php
namespace App\Http\Resources\Cloud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CloudFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'ext'       => $this->ext,
            'downloads' => $this->downloads,
            'name'      => $this->name,
        ];
    }
}
