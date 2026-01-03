<?php

namespace App\Models\Ticket;

use App\Enums\Ticket\TicketState;
use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'title',
        'category_id',
        'partner_id',
        'user_id',
        'attributes',
        'state'
    ];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
        'state'       => TicketState::class,
        'attributes'  => 'array'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
