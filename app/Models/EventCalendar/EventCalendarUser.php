<?php

namespace App\Models\EventCalendar;

use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventCalendarUser extends Model
{
    protected $table = 'event_calendar_user';

    protected $fillable = [
        'event_calendar_id',
        'user_id',
    ];

    public function eventCalendar(): BelongsTo
    {
        return $this->belongsTo(EventCalendar::class);
    }
}
