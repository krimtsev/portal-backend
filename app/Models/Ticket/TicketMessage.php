<?php

namespace App\Models\Ticket;

use App\Models\User\User;
use App\Models\Ticket\TicketFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessage extends Model
{
    use SoftDeletes;

    protected $table = 'tickets_messages';

    protected $fillable = [
        'questions',
        'text',
        'ticket_id',
        'user_id',
        'is_event',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class, 'ticket_message_id');
    }
}
