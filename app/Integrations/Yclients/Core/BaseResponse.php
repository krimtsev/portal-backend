<?php

namespace App\Integrations\Yclients\Core;

use App\Integrations\Yclients\YclientsException;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Throwable;

abstract class BaseResponse extends Data
{
    /**
     * Автоматическая сборка DTO из массива с жесткой валидацией структуры
     *
     * @throws YclientsException
     */
    /*public static function fromArray(array $response): static
    {
        $mapping = static::getInputMapping();
        $resolvedArgs = [];

        foreach ($mapping as $property => $dotPath) {
            // Безопасно извлекаем данные по любому уровню вложенности
            $value = data_get($response, $dotPath);

            // Если обязательного ключа нет в ответе API
            if ($value === null) {
                throw new YclientsException(
                    "Критическая ошибка структуры Yclients API. Отсутствует обязательное поле [{$dotPath}] для свойства [{$property}]."
                );
            }

            $resolvedArgs[$property] = $value;
        }

        // Динамически создаем объект, распаковывая именованные аргументы в конструктор
        return new static(...$resolvedArgs);
    }*/

    /**
     * Каждый дочерний DTO обязан вернуть карту путей JSON ответа
     */
/*    abstract protected static function getInputMapping(): array;*/

    /**
     * Экспорт DTO обратно в плоский массив (для контроллеров/Vue)
     */
/*    public function toArray(): array
    {
        return get_object_vars($this);
    }*/

    /**
     * Валидация коллекции объекта
     *
     * @throws YclientsException
     */
/*    public static function collectSafe(mixed $payload): DataCollection
    {
        try {
            $items = $payload['data'] ?? [];

            return self::collect($items);
        } catch (ValidationException $e) {
            $className = class_basename(static::class);

            throw new YclientsException(
                "Критическая ошибка структуры JSON в ответе Yclients API [{$className}]: " . $e->getMessage()
            );
        }
    }*/

    /**
     * Валидация одиночного объекта
     *
     * @param mixed $data
     * @return self
     * @throws YclientsException
     */
    /*public static function fromSafe(mixed $data): static
    {
        try {
            return static::from($data);
        } catch (Throwable $e) {
            $className = class_basename(static::class);

            throw new YclientsException(
                "Критическая ошибка структуры JSON в ответе Yclients API [{$className}]: " . $e->getMessage()
            );
        }
    }*/
}
