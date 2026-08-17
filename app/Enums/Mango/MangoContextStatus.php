<?php

namespace App\Enums\Mango;

enum MangoContextStatus: int
{
    case FAILED = 0;
    case SUCCESS = 1;

    public function label(): string
    {
        return match ($this) {
            self::FAILED  => 'Неуспешный',
            self::SUCCESS => 'Успешный',
        };
    }
}
