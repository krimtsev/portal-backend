<?php
namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'login'         => $this->login,
            'role'          => $this->role,
            'disabled'      => (bool)$this->disabled,
            'last_activity' => $this->last_activity?->format('Y-m-d H:i:s'),
            'access'   => $this->whenLoaded('access', function() {
                return [
                    'location_map' => (bool) ($this->access->location_map ?? false),
                ];
            }),
            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id'   => $this->partner->id,
                    'name' => $this->partner->name,
                ];
            }),
        ];
    }
}
