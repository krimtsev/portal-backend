<?php
namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class UserExportResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'login'         => $this->login,
            'role'          => $this->role,
            'disabled'      => (bool)$this->disabled,
            'last_activity' => $this->last_activity?->format('Y-m-d H:i:s'),

            'partner'       => $this->whenLoaded('partner', function () {
                return $this->partner->name ?? '-';
            }),
        ];
    }

}
