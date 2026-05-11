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
            'notes'   => $this->notes,
            'partner' => $this->whenLoaded('partner', function() {
                return [
                    'id'   => $this->partner->id,
                    'name' => $this->partner->name,
                ];
            }),
            'disabled' => (bool) $this->disabled,
            'access'   => $this->whenLoaded('access', function() {
                return [
                    'location_map' => (bool) ($this->access->location_map ?? false),
                ];
            }),
            'departments' => $this->whenLoaded('departments', function() {
                return $this->departments->pluck('department_slug')->toArray();
            }),
        ];
    }
}
