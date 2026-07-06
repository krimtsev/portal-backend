<?php

declare(strict_types=1);

namespace App\Http\Resources\Cloud;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CloudOptionTreeResource extends JsonResource
{
    /**
     * Преобразует данные папки в формат Tree Node (PrimeVue)
     */
    public function toArray(Request $request): array
    {
        $node = [
            'key'   => (int) $this['id'],
            'label' => $this['name'],
        ];

        if (!empty($this['children'])) {
            $node['children'] = self::collection($this['children'])->toArray($request);
        }

        return $node;
    }
}
