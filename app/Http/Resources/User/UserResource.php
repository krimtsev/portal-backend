<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Partner\PartnerResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'login'  => $this->login,
            'name'   => $this->name,
            'role'   => $this->role,
            'email'  => $this->email,
            'avatar' => $this->avatar,
            'partner' => new PartnerResource($this->whenLoaded('partner')),
        ];
    }
}
