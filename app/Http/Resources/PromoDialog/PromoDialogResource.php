<?php

namespace App\Http\Resources\PromoDialog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $template
 * @property numeric $disabled
 */
final class PromoDialogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description ?? "",
            'template'    => $this->template,
            'disabled'    => (bool) $this->disabled,
        ];
    }
}
