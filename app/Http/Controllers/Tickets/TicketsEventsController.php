<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketEvent;
use Illuminate\Http\Request;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketsEventsController extends Controller
{
    /**
     * Добавить запись изменений
     */
    public function create(Ticket $ticket, Request $request): void
    {
        $fields = ['state', 'category_id', 'partner_id'];
        $changes = [];

        foreach ($fields as $field) {
            if (
                $request->has($field) &&
                $ticket->{$field} !== $request->input($field)
            ) {
                $changes[$field] = [
                    'old' => $ticket->{$field},
                    'new' => $request->input($field),
                ];
            }
        }

        if (!empty($changes)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'changes'   => $changes,
            ]);
        }
    }
}
