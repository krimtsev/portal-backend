<?php

namespace App\Http\Resources\User;

use App\Constants\Timezone\Timezone;
use App\Http\Resources\PromoDialog\PromoDialogResource;
use App\Models\Promo\PromoDialog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Collection<int, PromoDialog>|null $promoDialogs
 */
class UserDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'login'  => $this->login,
                'role'   => $this->role,
                'name'   => $this->name,
                'avatar' => $this->avatar,
                'email'  => $this->email,
            ],
            'partner' => $this->whenLoaded('partner', function () {
                return $this->partner ? [
                    'id'       => $this->partner->id,
                    'name'     => $this->partner->name,
                    'disabled' => $this->partner->disabled,
                ] : null;
            }),
            'access' => [
                'location_map' => $this->access?->location_map,
            ],
            'promoDialogs' => $this->whenLoaded(
                'promoDialogs',
                fn () => PromoDialogResource::collection($this->promoDialogs)->resolve()
            ),
            'timeZoneName' => $this->time_zone_name ?? Timezone::DEFAULT,
        ];
    }
}
