<?php

namespace App\Models\Promo;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PromoDialogRead extends Model
{
    /**
     * @var string
     */
    protected $table = 'promo_dialog_reads';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'promo_dialog_id',
        'user_id',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            // Проставляем дату ознакомления, если её не передали явно
            $model->read_at ??= now();
        });
    }

    public function promoDialog(): BelongsTo
    {
        return $this->belongsTo(PromoDialog::class, 'promo_dialog_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
