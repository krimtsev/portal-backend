<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'login'         => $this->login,
            'role'          => $this->role,
            'email'         => $this->email,
            'disabled'      => (bool) $this->disabled,
            'last_activity' => $this->last_activity?->format('Y-m-d H:i:s'),
            'partner'       => $this->whenLoaded('partner', function () {
                return $this->partner->name ?? '-';
            }),
            'departments' => $this->whenLoaded('departments', function () {
                return $this->departments->pluck('id');
            }),
        ];
    }
}
