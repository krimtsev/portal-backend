<?php

declare(strict_types=1);

namespace App\Enums\User;

enum UserRole: string
{
    case Sysadmin = 'sysadmin';
    case Admin = 'admin';
    case User = 'user';
}
