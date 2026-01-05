<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $messages = TicketMessageResource::collection($this->messages);
        $events   = TicketEventResource::collection($this->events);

        $timeline = collect()
            ->merge($messages)
            ->merge($events)
            ->sortBy('created_at')
            ->values();

        return [
            'id'    => $this->id,
            'title' => $this->title,
            'type'  => $this->type,
            'state' => $this->state->value,

            'attributes' => $this->attributes,

            'category' => [
                'id'    => $this->category?->id,
                'title' => $this->category?->title,
            ],

            'partner' => [
                'id'   => $this->partner?->id,
                'name' => $this->partner?->name,
            ],

            'user' => [
                'login' => $this->user?->login,
                'name'  => $this->user?->name,
            ],

            'timeline' => $timeline,
        ];
    }
}
