<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'state' => [
                'key'   => $this->state->value, // in_progress
                'value' => str_replace('_', '-', $this->state->value), // in-progress
            ],

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
                    'id'   => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
        ];
    }
}
