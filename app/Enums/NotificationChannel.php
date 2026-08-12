<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationChannel: string
{
    case TELEGRAM = 'telegram';

    public function flagColumn(): string
    {
        return "send_{$this->value}"; // send_telegram
    }

    public function identifierColumn(): string
    {
        return "{$this->value}_chat_id"; // telegram_chat_id
    }
}
