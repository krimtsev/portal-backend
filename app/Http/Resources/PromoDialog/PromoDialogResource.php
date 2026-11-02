<?php

namespace App\Http\Resources\PromoDialog;

use App\Enums\PromoDialog\PromoDialogType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string|null $code
 * @property string $title
 * @property string|null $description
 * @property PromoDialogType $type
 */
final class PromoDialogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type->value,
        ];
    }
}
