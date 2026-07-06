<?php

declare(strict_types=1);

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketExportResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => $this->type,
            'state'         => $this->state,
            'department_id' => $this->department_id,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
