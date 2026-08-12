<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerNotificationChannel extends Model
{
    protected $fillable = [
        'partner_id',
        'send_telegram',
        'telegram_chat_id',
        'check_payment',
        'payment_date',
    ];

    protected $casts = [
        'send_telegram'    => 'boolean',
        'telegram_chat_id' => 'string',
        'check_payment'    => 'boolean',
        'payment_date'     => 'date',
    ];

    /**
     * Партнер, которому принадлежит канал
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Проверка, активна ли оплата для канала
     */
    public function isPaid(): bool
    {
        if (!$this->check_payment) {
            return true;
        }

        return $this->payment_date !== null && $this->payment_date->isFuture();
    }

    /**
     * Можно ли сейчас отправлять сообщения в этот Telegram-канал
     */
    public function canSendTelegram(): bool
    {
        return $this->send_telegram
            && $this->telegram_chat_id !== null
            && $this->isPaid();
    }

    /**
     * Scope: Проверка наличия активной подписки (или бесплатного использования)
     */
    public function scopeHasActiveSubscription(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('check_payment', false)
                ->orWhere(function (Builder $sq) {
                    $sq->where('check_payment', true)
                        ->whereDate('payment_date', '>', now());
                });
        });
    }
}
