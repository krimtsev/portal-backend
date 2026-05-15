<?php

namespace App\Notifications\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Spatie\Mjml\MjmlMessage;

class TicketMessageAddNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $ticketMessage
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MjmlMessage
    {
        return (new MjmlMessage())
            ->subject("Новое сообщение в заявке #{$this->ticket->id}")
            ->view('emails.tickets.message_added', [
                'ticket' => $this->ticket,
                'comment' => $this->ticketMessage,
                'user' => $notifiable
            ]);
    }
}
