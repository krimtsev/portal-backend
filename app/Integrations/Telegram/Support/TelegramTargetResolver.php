<?php

declare(strict_types=1);

namespace App\Integrations\Telegram\Support;

use App\Integrations\Telegram\DTO\TelegramTarget;
use RuntimeException;

final readonly class TelegramTargetResolver
{
    /**
     * @param  string|int|null  $defaultChatId  Динамический ID чата (например, из БД)
     * @param  string  $defaultBotName  Бот по умолчанию, если канал не указан
     * @param  string|null  $channelName  Имя канала из конфига (например, 'staff_updates')
     */
    public static function resolve(
        string|int|null $defaultChatId = null,
        string $defaultBotName = 'main',
        ?string $channelName = null,
    ): TelegramTarget {
        // Включен debug режим
        if (config('telegram.debug.enabled') === true) {
            $debugChatId = config('telegram.debug.chat_id');

            if (!$debugChatId) {
                throw new RuntimeException('Telegram debug is enabled, but [telegram.debug.chat_id] is missing.');
            }

            return new TelegramTarget(
                chatId: $debugChatId,
                botName: config('telegram.debug.bot'),
            );
        }

        // Статичный канал из конфига (например, системные логи/уведомления)
        if ($channelName !== null) {
            $config = config("telegram.channels.{$channelName}");
            $chatId = $config['chat_id'] ?? $defaultChatId;
            $botName = $config['bot'] ?? $defaultBotName;

            if (!$chatId) {
                throw new RuntimeException("Telegram channel [{$channelName}] chat_id is not configured.");
            }

            return new TelegramTarget(
                chatId: $chatId,
                botName: $botName,
            );
        }

        if (!$defaultChatId) {
            throw new RuntimeException('Telegram chat_id is required but not provided.');
        }

        return new TelegramTarget(
            chatId: $defaultChatId,
            botName: $defaultBotName,
        );
    }
}
