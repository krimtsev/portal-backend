<?php

namespace App\Models\Mango;

use App\Enums\Mango\MangoContextStatus;
use App\Enums\Mango\MangoContextType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MangoCall extends Model
{
    protected $table = 'mango_calls';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_id',
        'context_type',
        'context_status',
        'caller_number',
        'called_number',
        'context_start_time',
        'duration',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context_type'       => MangoContextType::class,
            'context_status'     => MangoContextStatus::class,
            'duration'           => 'integer',
            'context_start_time' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Локальные области запросов)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Фильтр только входящих звонков.
     */
    public function scopeIncoming(Builder $query): Builder
    {
        return $query->where('context_type', MangoContextType::INCOMING);
    }

    /**
     * Scope: Фильтр успешных звонков.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('context_status', MangoContextStatus::SUCCESS);
    }

    /**
     * Scope: Фильтр неуспешных (пропущенных/сброшенных) звонков.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('context_status', MangoContextStatus::FAILED);
    }

    /**
     * Scope: Фильтрация по периоду даты начала звонка.
     */
    public function scopePeriod(Builder $query, string|\DateTimeInterface $from, string|\DateTimeInterface $to): Builder
    {
        return $query->whereBetween('context_start_time', [$from, $to]);
    }
}
