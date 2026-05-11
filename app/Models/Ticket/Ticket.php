<?php

namespace App\Models\Ticket;

use App\Enums\Department;
use App\Enums\Ticket\TicketState;
use App\Enums\Ticket\TicketType;
use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'attributes',
        'type',
        'department',
        'partner_id',
        'user_id',
        'state'
    ];

    protected $table = 'tickets';

    protected $casts = [
        'partner_id'  => 'integer',
        'created_at'  => 'date:Y-m-d',
        'state'       => TicketState::class,
        'type'        => TicketType::class,
        'department'  => Department::class,
        'attributes'  => 'array'
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class, 'ticket_id');
    }
}
