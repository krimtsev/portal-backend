<?php

namespace App\Models\Partner;

use App\Models\User\User;
use App\Models\Partner\PartnerTelnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

    protected $table = 'partners';

    protected $casts = [
        'pay_end' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'organization',
        'inn',
        'name',
        'contract_number',
        'email',
        'address',
        'start_at',
        'yclients_id',
        'disabled',
        'group_id'
    ];

    public function telnums(): HasMany
    {
        return $this->hasMany(PartnerTelnum::class, 'partner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получение активных партнеров с выборкой динамических полей
     *
     * @param Builder $query
     * @param array $fields - какие поля вернуть
     * @return Builder
     */
    public function scopeActiveWhere($query, array $fields = ['id', 'name']): Builder
    {
        $query->where('disabled', 0);

        $selectFields = array_filter(
            $fields,
            fn($field) => in_array($field, $this->fillable) || $field === 'id'
        );

        return $query->select($selectFields);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(PartnerGroup::class, 'group_id');
    }
}
