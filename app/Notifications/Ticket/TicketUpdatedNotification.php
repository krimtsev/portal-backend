<?php

namespace App\Notifications\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public ?TicketMessage $ticketMessage,
        public bool $isStatusChanged = false,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $subjectKey = $this->getSubjectKey();
        $subject = trans($subjectKey, ['id' => $this->ticket->id]);

        $status = trans("common.status.{$this->ticket->state->value}");

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.tickets.new-comment', [
                'ticket'          => $this->ticket,
                'ticketMessage'   => $this->ticketMessage,
                'isStatusChanged' => $this->isStatusChanged,
                'status'          => $status,
                'user'            => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    private function getSubjectKey(): string
    {
        if ($this->ticketMessage && $this->isStatusChanged) {
            return 'emails.ticket.subject.all_changed';
        }

        if ($this->isStatusChanged) {
            return 'emails.ticket.subject.status_changed';
        }

        return 'emails.ticket.subject.text_changed';
    }
}
