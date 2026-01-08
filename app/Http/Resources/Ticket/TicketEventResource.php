<?php
namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TicketEventResource extends JsonResource
{
    protected $partners;
    protected $categories;

    public function __construct($resource, $partners = null, $categories = null)
    {
        parent::__construct($resource);
        $this->partners   = $partners;
        $this->categories = $categories;
    }

    public function toArray(Request $request): array
    {
        $changes = $this->changes;

        if (isset($changes['partner_id'])) {
            $changes['partner'] = [
                'old' => $this->partners[$changes['partner_id']['old']]?->name ?? null,
                'new' => $this->partners[$changes['partner_id']['new']]?->name ?? null,
            ];
            unset($changes['partner_id']);
        }

        // Category
        if (isset($changes['category_id'])) {
            $changes['category'] = [
                'old' => $this->categories[$changes['category_id']['old']]?->title ?? null,
                'new' => $this->categories[$changes['category_id']['new']]?->title ?? null,
            ];
            unset($changes['category_id']);
        }

        return [
            'type'       => 'event',
            'id'         => $this->id,
            'changes'    => $changes,
            'created_at' => $this->created_at,

            'user' => [
                'login' => $this->user?->login,
                'name'  => $this->user?->name,
            ],
        ];
    }
}
