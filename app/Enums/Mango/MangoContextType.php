<?php

namespace App\Enums\Mango;

enum MangoContextType: int
{
    case INCOMING = 1;
    case OUTGOING = 2;
    case INTERNAL = 3;

    public function label(): string
    {
        return match ($this) {
            self::INCOMING => 'Входящий',
            self::OUTGOING => 'Исходящий',
            self::INTERNAL => 'Внутренний',
        };
    }
}
