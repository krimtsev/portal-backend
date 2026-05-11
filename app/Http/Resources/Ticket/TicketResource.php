<?php
namespace App\Http\Resources\Ticket;

use App\Enums\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Partner\Partner;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $messages = TicketMessageResource::collection($this->messages)->resolve();

        $partners = Partner::all()->keyBy('id');

        $events = collect($this->events)->map(function ($event) use ($partners) {
            return (new TicketEventResource($event, $partners))->resolve();
        });

        $timeline = collect()
            ->merge($messages)
            ->merge($events)
            ->sortBy('created_at')
            ->values();

        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'type'       => $this->type,
            'state'      => $this->state->value,
            'attributes' => $this->attributes,
            'department' => $this->department,

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
