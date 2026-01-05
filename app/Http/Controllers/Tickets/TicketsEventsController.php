<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketEvent;
use Illuminate\Http\Request;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketsEventsController extends Controller
{
    function create(Ticket $ticket, Request $request): void
    {
        $changes = [];

        if (
            $request->has('state') &&
            $ticket->state !== $request->state
        ) {
            $changes['state'] = [
                'old' => $ticket->state,
                'new' => $request->state,
            ];
        }

        if (
            $request->has('category_id') &&
            $ticket->category_id !== $request->category_id
        ) {
            $changes['category_id'] = [
                'old' => $ticket->category_id,
                'new' => $request->category_id,
            ];
        }

        if (
            $request->has('partner_id') &&
            $ticket->partner_id !== $request->partner_id
        ) {
            $changes['partner_id'] = [
                'old' => $ticket->partner_id,
                'new' => $request->partner_id,
            ];
        }

        if ($changes) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'changes'   => $changes,
            ]);
        }
    }
}
