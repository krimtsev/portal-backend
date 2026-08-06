<?php

namespace App\Integrations\Telegram;

use App\Integrations\Telegram\Transport\TelegramTransport;
use InvalidArgumentException;

final class TelegramManager
{
    /** @var array<string, TelegramBot> */
    private array $bots = [];

    public function __construct(
        private readonly array $config,
        private readonly TelegramTransport $transport
    ) {}

    public function bot(?string $name = null): TelegramBot
    {
        $name ??= $this->config['default'] ?? 'main';

        if (!isset($this->bots[$name])) {
            $token = $this->config['bots'][$name]['token'] ?? null;

            if (!$token) {
                throw new InvalidArgumentException("Telegram bot [{$name}] is not configured.");
            }

            $this->bots[$name] = new TelegramBot($token, $this->transport);
        }

        return $this->bots[$name];
    }

    public function __call(string $method, array $parameters)
    {
        return $this->bot()->$method(...$parameters);
    }
}
