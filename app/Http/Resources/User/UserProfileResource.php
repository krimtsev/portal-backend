<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'login'       => $this->login,
            'name'        => $this->name,
            'role'        => $this->role,
            'email'       => $this->email,
            'avatar'      => $this->avatar,
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->pluck('id')->toArray();
            }),
        ];
    }
}
