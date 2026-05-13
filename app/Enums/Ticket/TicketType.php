<?php

namespace App\Enums\Ticket;

enum TicketType: string
{
    case Administrator = 'administrator';
    case Blacklist = 'blacklist';
    case Certificate = 'certificate';
    case Design = 'design';
    case Flagman = 'flagman';
    case General = 'general';
    case Opening = 'opening';
    case Specialist = 'specialist';

    public static function default(): self
    {
        return self::General;
    }
}
