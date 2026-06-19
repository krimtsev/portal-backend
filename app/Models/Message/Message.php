<?php

namespace App\Models\Message;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
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
        'days'     => 'array',
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
}
