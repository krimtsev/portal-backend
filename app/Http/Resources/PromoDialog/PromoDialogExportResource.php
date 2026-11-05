<?php

namespace App\Http\Resources\PromoDialog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin \App\Models\User\User
 *
 * Ожидает, что в модель уже подмешан атрибут `read_at`
 * (через leftJoin на promo_dialog_reads в контроллере).
 */
final class PromoDialogExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'login'        => $this->login,
            'name'         => $this->name,
            'disabled'     => (bool) $this->disabled,
            'partner_name' => $this->partner?->name ?: $this->partner?->organization,
            'is_read'      => $this->read_at !== null,
            'read_at'      => $this->read_at
                ? Carbon::parse($this->read_at)->toIso8601String()
                : null,
        ];
    }
}
