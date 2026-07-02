<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YcRecordGoodsTransaction extends Model
{
    protected $table = 'yc_record_goods_transactions';

    protected $fillable = [
        'record_id',
        'transaction_id',
        'company_id',
        'title',
        'amount',
        'cost_per_unit',
        'cost',
        'manual_cost',
        'discount',
        'master_id',
        'storage_id',
        'good_id',
        'loyalty_abonement_id',
        'loyalty_certificate_id',

        'datetime',
        'record_staff_id',
        'attendance',
    ];

    protected function casts(): array
    {
        return [
            'record_id'              => 'integer',
            'transaction_id'         => 'integer',
            'company_id'             => 'integer',
            'amount'                 => 'integer',
            'cost_per_unit'          => 'float',
            'cost'                   => 'float',
            'manual_cost'            => 'float',
            'discount'               => 'float',
            'master_id'              => 'integer',
            'storage_id'             => 'integer',
            'good_id'                => 'integer',
            'loyalty_abonement_id'   => 'integer',
            'loyalty_certificate_id' => 'integer',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(YcRecord::class, 'record_id', 'record_id');
    }
}
