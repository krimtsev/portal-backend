<?php

namespace App\Notifications\User;

use App\Models\User\User;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Бесконечное количество попыток
    public int $tries = 0;

    // Лимит реальных ошибок (например, если внешний сервис лег и отдал 500)
    public int $maxExceptions = 3;

    public function __construct(
        public User $user
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
     * Каналы отправки уведомления.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Представление письма.
     */
    public function toMail($notifiable): MailMessage
    {
        $subject = trans('emails.user.subject.password_changed');

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.users.password-changed', [
                'user' => $this->user,
            ]);
    }
}
