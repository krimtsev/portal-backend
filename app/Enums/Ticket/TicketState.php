<?php

namespace App\Enums\Ticket;

enum TicketState: string
{
    case New        = 'new';
    case InProgress = 'in_progress';
    case Waiting    = 'waiting';
    case Success    = 'success';
    case Closed     = 'closed';
    case Cancel     = 'cancel';

    public static function default(): self
    {
        return self::New;
    }
}
