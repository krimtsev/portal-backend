<?php

declare(strict_types=1);

namespace App\Enums\User;

enum UserRole: string
{
    case Sysadmin = 'sysadmin';
    case Admin = 'admin';
    case OfficeManager = 'office_manager';
    case Marketing = 'marketing';
    case Support = 'support';
    case User = 'user';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
