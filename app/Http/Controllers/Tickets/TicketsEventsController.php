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
    public function create(Ticket $original, Ticket $updated): void
    {
        $fields = ['title', 'state', 'category_id', 'partner_id'];
        $changes = [];

        foreach ($fields as $field) {
            if ($original->$field !== $updated->$field) {
                $changes[$field] = [
                    'old' => $original->$field,
                    'new' => $updated->$field,
                ];
            }
        }

        if ($changes) {
            TicketEvent::create([
                'ticket_id' => $updated->id,
                'user_id'   => Auth::id(),
                'changes'   => $changes,
            ]);
        }
    }
}
