<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'type'       => $this->type,
            'state'      => $this->state->value,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            'last_message_at' => $this->last_message_at,

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id'    => $this->category->id,
                    'title' => $this->category->title,
                ];
            }),

            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id'   => $this->partner->id,
                    'name' => $this->partner->name,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'login' => $this->user->login,
                    'name'  => $this->user->name,
                ];
            }),
        ];
    }
}
