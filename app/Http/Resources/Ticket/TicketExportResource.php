<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TicketExportResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => $this->type,
            'state'         => $this->state,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),

            'category' => $this->whenLoaded('category', function () {
                return $this->category->title;
            }),
        ];
    }

}
