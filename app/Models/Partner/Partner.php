<?php

namespace App\Models\Partner;

use App\Models\User\User;
use App\Models\Partner\PartnerTelnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

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
        'disabled'
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $fields - какие поля вернуть
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveWhere($query, array $fields = ['id','name'])
    {
        $query->where('disabled', 0);

        $selectFields = array_filter(
            $fields,
            fn($field) => in_array($field, $this->fillable) || $field === 'id'
        );

        return $query->select($selectFields);
    }
}

