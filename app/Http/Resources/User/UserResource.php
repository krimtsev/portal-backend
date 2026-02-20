<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'    => $this->name,
            'login'   => $this->login,
            'role'    => $this->role,
            'email'   => $this->email,
            'partner' => $this->whenLoaded('partner', function() {
                return [
                    'id'   => $this->partner->id,
                    'name' => $this->partner->name,
                ];
            }),
            'disabled' => (bool) $this->disabled
        ];
    }
}
