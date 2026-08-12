<?php

declare(strict_types=1);

namespace App\Integrations\Telegram\DTO;

final readonly class TelegramTarget
{
    public function __construct(
        public string|int $chatId,
        public string $botName,
    ) {}
}
