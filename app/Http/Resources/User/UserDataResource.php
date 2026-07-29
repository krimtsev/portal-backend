<?php

namespace App\Http\Resources\User;

use App\Constants\Timezone\Timezone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'timeZoneName' => $this->time_zone_name ?? Timezone::DEFAULT_TIMEZONE,
        ];
    }
}
