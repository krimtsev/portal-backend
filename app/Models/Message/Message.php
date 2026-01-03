<?php

namespace App\Models\Message;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'disabled',
        'user_id',
        'partner_id',
        'days',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'days' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeVisibleFor($query, $user)
    {
        $today = now()->day;

        return $query->where('disabled', false)
            ->where(function ($q) use ($user, $today) {

                // Сообщения без фильтров — показываем всем
                $q->where(function ($sub) {
                    $sub->whereNull('user_id')
                        ->whereNull('partner_id')
                        ->whereNull('days');
                })

                    // Сообщения с фильтрами — должны совпадать все указанные
                    ->orWhere(function ($sub) use ($user, $today) {
                        $sub->where(function ($s) use ($user) {
                            $s->whereNull('user_id')
                                ->orWhere('user_id', $user->id);
                        })
                            ->where(function ($s) use ($user) {
                                $s->whereNull('partner_id')
                                    ->orWhere('partner_id', $user->partner_id);
                            })
                            ->where(function ($s) use ($today) {
                                $s->whereNull('days')
                                    ->orWhereJsonContains('days', $today);
                            });
                    });
            });
    }

    /**
     * Проверка видимости конкретного сообщения для пользователя.
     */
    /*public function isVisibleFor($user): bool
    {
        if ($this->disabled) {
            return false;
        }

        $todayDay = now()->day;
        $userId = $user?->id;
        $partnerId = $user?->partner_id;

        // Нет фильтров → показываем всем
        if (!$this->user_id && !$this->partner_id && empty($this->days)) {
            return true;
        }

        if ($this->user_id && $this->user_id !== $userId) {
            return false;
        }

        if ($this->partner_id && $this->partner_id !== $partnerId) {
            return false;
        }

        if (!empty($this->days) && !in_array($todayDay, $this->days)) {
            return false;
        }

        return true;
    }*/
}
