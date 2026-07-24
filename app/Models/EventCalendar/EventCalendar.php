<?php

namespace App\Models\EventCalendar;

use App\Models\Department\Department;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EventCalendar extends Model
{
    /**
     * Атрибуты, для которых разрешено массовое заполнение.
     */
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'department_id',
        'start_at',
        'end_at',
    ];

    /**
     * Автоматическое приведение типов для дат.
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Отдел, к которому относится событие.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function responsibleUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_calendar_user');
    }

    public function eventCalendarUsers(): HasMany
    {
        return $this->hasMany(EventCalendarUser::class, 'event_calendar_id');
    }
}
