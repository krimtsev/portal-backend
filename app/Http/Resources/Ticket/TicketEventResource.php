<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TicketEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type'       => 'event',
            'id'         => $this->id,
            'changes'    => $this->changes,
            'created_at' => $this->created_at,

            'user' => [
                'login' => $this->user?->login,
                'name'  => $this->user?->name,
            ],
        ];
    }
}
