<?php

namespace App\Integrations\Yclients\Core;

use JsonSerializable;

abstract class BaseRequest implements JsonSerializable
{
    public static function make(...$args): static
    {
        return new static(...$args);
    }

    /**
     * Единая логика очистки null-значений для всех наследников
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), fn ($val) => $val !== null);
    }

    /**
     * На случай, если где-то внутри интеграции массив нужен явно без json_encode
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
