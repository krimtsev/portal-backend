<?php

namespace App\Integrations\Telegram;

use App\Integrations\Telegram\DTO\TelegramResponse;
use App\Integrations\Telegram\Enums\ParseMode;
use App\Integrations\Telegram\Transport\TelegramTransport;

final readonly class TelegramBot
{
    public function __construct(
        private string $token,
        private TelegramTransport $transport
    ) {}

    public function sendMessage(array $parameters): TelegramResponse
    {
        return $this->request('sendMessage', $parameters);
    }

    public function sendPhoto(array $parameters): TelegramResponse
    {
        return $this->request('sendPhoto', $parameters);
    }

    public function sendDocument(array $parameters): TelegramResponse
    {
        return $this->request('sendDocument', $parameters);
    }

    public function getMe(): TelegramResponse
    {
        return $this->request('getMe');
    }

    public function getWebhookInfo(): TelegramResponse
    {
        return $this->request('getWebhookInfo');
    }

    private function request(string $method, ?array $parameters = []): TelegramResponse
    {
        $parameters['parse_mode'] ??= ParseMode::HTML->value;

        return $this->transport->send($this->token, $method, $parameters);
    }
}
