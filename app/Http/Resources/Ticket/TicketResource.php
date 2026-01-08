<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Partner\Partner;
use App\Models\Ticket\TicketCategory;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $messages = TicketMessageResource::collection($this->messages)->resolve();

        $partners = Partner::all()->keyBy('id');
        $categories = TicketCategory::all()->keyBy('id');

        $events = collect($this->events)->map(function ($event) use ($partners, $categories) {
            return (new TicketEventResource($event, $partners, $categories))->resolve();
        });

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
