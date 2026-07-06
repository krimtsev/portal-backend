<?php

declare(strict_types=1);

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketFileResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'ext'   => $this->ext,
            'path'  => $this->path,
            'name'  => $this->name,
        ];
    }
}
