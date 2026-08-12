<?php

declare(strict_types=1);

namespace App\Enums\Report;

enum ClientReportType: string
{
    case NEW_CLIENTS = 'new';
    case RETURNED_CLIENTS = 'returned';
    case LOST_CLIENTS = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NEW_CLIENTS      => 'Новые клиенты',
            self::RETURNED_CLIENTS => 'Повторные клиенты',
            self::LOST_CLIENTS     => 'Потерянные клиенты',
        };
    }

    public function daysSettingKey(): string
    {
        return match ($this) {
            self::NEW_CLIENTS      => 'new_clients_days',
            self::RETURNED_CLIENTS => 'returned_clients_days',
            self::LOST_CLIENTS     => 'lost_clients_days',
        };
    }
}
