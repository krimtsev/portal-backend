<?php

namespace App\Integrations\Yclients\Core;

use App\Integrations\Yclients\YclientsException;

abstract class BaseResponse
{
    /**
     * Автоматическая сборка DTO из массива с жесткой валидацией структуры
     *
     * @throws YclientsException
     */
    public static function fromArray(array $response): static
    {
        $mapping = static::getInputMapping();
        $resolvedArgs = [];

        foreach ($mapping as $property => $dotPath) {
            // Безопасно извлекаем данные по любому уровню вложенности
            $value = data_get($response, $dotPath);

            // КРИТИЧЕСКАЯ ЗАЩИТА: Если обязательного ключа нет в ответе API
            if ($value === null) {
                throw new YclientsException(
                    "Критическая ошибка структуры Yclients API. Отсутствует обязательное поле [{$dotPath}] для свойства [{$property}]."
                );
            }

            $resolvedArgs[$property] = $value;
        }

        // Динамически создаем объект, распаковывая именованные аргументы в конструктор
        return new static(...$resolvedArgs);
    }

    /**
     * Каждый дочерний DTO обязан вернуть карту путей JSON ответа
     */
    abstract protected static function getInputMapping(): array;

    /**
     * Экспорт DTO обратно в плоский массив (для контроллеров/Vue)
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
