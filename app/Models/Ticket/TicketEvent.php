<?php

namespace App\Models\Ticket;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketEvent extends Model
{
    use SoftDeletes;

    protected $table = 'tickets_events';

    protected $fillable = [
        'ticket_id',
        'changes',
        'user_id',
    ];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
