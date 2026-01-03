<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketFile extends Model
{
    protected $table = 'tickets_files';

    protected $fillable = [
        'title',
        'name',
        'origin',
        'path',
        'type',
        'ext',
        'ticket_message_id',
    ];
}
