<?php

declare(strict_types=1);

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type'       => 'message',
            'id'         => $this->id,
            'text'       => $this->text,
            'created_at' => $this->created_at,

            'user' => [
                'login' => $this->user?->login,
                'name'  => $this->user?->name,
            ],

            'files' => TicketFileResource::collection($this->files),
        ];
    }
}
