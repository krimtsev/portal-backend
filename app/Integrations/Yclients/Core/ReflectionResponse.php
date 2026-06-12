<?php

namespace App\Integrations\Yclients\Core;

use App\Integrations\Yclients\Core\Attributes\CastToArrayOf;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/*
    TODO: Временно оставляем рефлексию, для каста DTO через атрибуты
    public function __construct(
        #[CastToArrayOf(ServiceDTO::class)]
        public array $services = [],
    ) {}
*/

abstract class ReflectionResponse
{
    /**
     * Автоматически собирает объект DTO из сырого массива на основе типов конструктора.
     */
    public static function from(array $data): static
    {
        $class = static::class;
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $constructorArgs = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // Забираем значение из массива
            $value = $data[$name] ?? null;

            // Если значения нет, проверяем дефолты или nullable
            if ($value === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $constructorArgs[$name] = $parameter->getDefaultValue();
                } elseif ($type?->allowsNull()) {
                    $constructorArgs[$name] = null;
                } else {
                    throw new RuntimeException("Missing required property: {$class}::\${$name}");
                }

                continue;
            }

            // Обработка типизированных объектов (например, ?EmployeeDTO)
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $nestedClass = $type->getName();

                if (is_array($value) && is_subclass_of($nestedClass, self::class)) {
                    $constructorArgs[$name] = $nestedClass::from($value);
                } else {
                    $constructorArgs[$name] = $value;
                }

                continue;
            }

            // Обработка массивов данных (включая ассоциативные с динамическими ключами)
            if ($type instanceof ReflectionNamedType && $type->getName() === 'array' && is_array($value)) {
                $attributes = $parameter->getAttributes(CastToArrayOf::class);

                if (!empty($attributes)) {
                    $castToClass = $attributes[0]->newInstance()->className;

                    if (is_subclass_of($castToClass, self::class)) {
                        $mappedArray = [];
                        foreach ($value as $key => $item) {
                            // Сохраняем динамические ключи и гидрируем вложенные структуры
                            $mappedArray[$key] = is_array($item) ? $castToClass::from($item) : $item;
                        }
                        $constructorArgs[$name] = $mappedArray;

                        continue;
                    }
                }
            }

            // Для плоских скалярных типов оставляем значение как есть (без лишнего мусора)
            $constructorArgs[$name] = $value;
        }

        return new $class(...$constructorArgs);
    }
}
