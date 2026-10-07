<?php

declare(strict_types=1);

namespace App\Integrations\Yclients\Support;

use RuntimeException;

final readonly class YclientsDataResolver
{
    /**
     * Валидирует ответ от API и извлекает payload.
     *
     * @throws RuntimeException
     */
    public static function extractData(mixed $response): array
    {
        if (!is_array($response) || !array_key_exists('data', $response) || !is_array($response['data'])) {
            $errorMessage = $response['meta']['message']
                ?? $response['message']
                ?? 'Некорректная структура ответа или отсутствие ключа data';

            throw new RuntimeException("Ошибка API YClients при запросе: {$errorMessage}");
        }

        return $response['data'];
    }
}
