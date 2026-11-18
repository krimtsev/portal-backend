<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'disabled',
        'expires_at',
        'user_id',
    ];

    protected $casts = [
        'disabled'   => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Только активные и не истёкшие акции.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('disabled', false)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
