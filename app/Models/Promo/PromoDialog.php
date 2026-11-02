<?php

namespace App\Models\Promo;

use App\Enums\PromoDialog\PromoDialogType;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PromoDialogType $type
 * @property bool $disabled
 */
final class PromoDialog extends Model
{
    /**
     * @var string
     */
    protected $table = 'promo_dialogs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'disabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'     => PromoDialogType::class,
            'disabled' => 'boolean',
        ];
    }

    public function reads(): HasMany
    {
        return $this->hasMany(PromoDialogRead::class, 'promo_dialog_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'promo_dialog_reads',
            'promo_dialog_id',
            'user_id'
        )->withPivot('read_at')->withTimestamps();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('disabled', false);
    }

    public function scopeNotReadBy(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave(
            'reads',
            fn (Builder $query) => $query->where('user_id', $userId)
        );
    }
}
