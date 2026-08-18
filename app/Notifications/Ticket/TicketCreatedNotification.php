<?php

namespace App\Notifications\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketMessage;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Бесконечное количество попыток
    public int $tries = 0;

    // Лимит реальных ошибок (например, если внешний сервис лег и отдал 500)
    public int $maxExceptions = 3;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public TicketMessage $ticketMessage
    ) {}

    public function middleware(): array
    {
        if (config('mail.rate_limit.enabled', false)) {
            return [new RateLimited('external_mailer')];
        }

        return [];
    }

    public function retryUntil(): ?DateTimeInterface
    {
        if (config('mail.rate_limit.enabled', false)) {
            return now()->addDay();
        }

        return null;
    }

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
        $subject = trans('emails.ticket.subject.created', ['id' => $this->ticket->id]);

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.tickets.new-ticket', [
                'ticket'        => $this->ticket,
                'ticketMessage' => $this->ticketMessage,
                'user'          => $notifiable,
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
}
