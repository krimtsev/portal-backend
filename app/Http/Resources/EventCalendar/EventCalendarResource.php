<?php

namespace App\Http\Resources\EventCalendar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'start_at'    => $this->start_at?->format('Y-m-d'),
            'end_at'      => $this->end_at?->format('Y-m-d'),
            'department_id'  => $this->department_id,

            'responsible_user_ids' => $this->whenLoaded('eventCalendarUsers', function () {
                return $this->eventCalendarUsers->pluck('user_id')->values()->all();
            }),
        ];
    }
}
