<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () { return "Hello World"; });

Route::get('/email-template', function () {
    $ticket = \App\Models\Ticket\Ticket::with('user')->first();

    if (!$ticket) {
        return "В базе данных пока нет ни одного тикета.";
    }

    $firstMessage = $ticket->messages->first();

    $notification = new \App\Notifications\Ticket\TicketCreatedNotification($ticket, $firstMessage);

    $user = \App\Models\User\User::first();

    return $notification->toMail($user)->render();
});
