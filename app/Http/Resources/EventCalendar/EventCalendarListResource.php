<?php

namespace App\Http\Resources\EventCalendar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCalendarListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'start_at'    => $this->start_at?->format('Y-m-d'),
            'end_at'      => $this->end_at?->format('Y-m-d'),

            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id'   => $this->user->id,
                    'name' => $this->user->name,
                ] : null;
            }),

            'department' => $this->whenLoaded('department', function () {
                return $this->department ? [
                    'id'    => $this->department->id,
                    'title' => $this->department->title,
                ] : null;
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
