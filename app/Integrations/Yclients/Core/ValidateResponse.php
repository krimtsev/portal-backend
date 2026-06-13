<?php

namespace App\Integrations\Yclients\Core;

use App\Integrations\Yclients\YclientsException;
use Illuminate\Support\Facades\Validator;

abstract class ValidateResponse
{
    abstract protected static function rules(): array;

    abstract protected static function build(array $validated): static;

    protected static function casts(): array
    {
        return [];
    }

    /**
     * @throws YclientsException
     */
    public static function from(array $data): ?static
    {
        $validator = Validator::make($data, static::rules());

        if ($validator->fails()) {
            $dto = static::class;
            $errors = json_encode($validator->errors()->toArray(), JSON_UNESCAPED_UNICODE);
            throw new YclientsException(
                "Критическая ошибка валидации [{$dto}]: " . $errors
            );
        }

        $cleanData = $validator->validated();

        foreach (static::casts() as $key => $targetClass) {
            if (!isset($cleanData[$key])) {
                continue;
            }

            if (is_array($targetClass)) {
                // Если указан массив, например: 'services_links' => [ServiceDTO::class]
                $dtoClass = $targetClass[0];
                $cleanData[$key] = array_filter(
                    array_map(fn ($item) => $dtoClass::from($item), $cleanData[$key])
                );
            } else {
                // Если это одиночный объект: 'employee' => EmployeeDTO::class
                $cleanData[$key] = $targetClass::from($cleanData[$key]);
            }
        }

        return static::build($cleanData);
    }
}
