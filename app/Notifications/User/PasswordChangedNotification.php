<?php

namespace App\Notifications\User;

use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

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
