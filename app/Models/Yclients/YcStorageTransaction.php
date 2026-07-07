<?php

namespace App\Models\Yclients;

use Illuminate\Database\Eloquent\Model;

class YcStorageTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_storage_transactions';

    /**
     * @var string
     */
    protected $primaryKey = 'transaction_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'company_id',
        'document_id',
        'type_id',
        'type',
        'operation_unit_type',
        'amount',
        'create_date',
        'cost_per_unit',
        'cost',
        'discount',
        'comment',
        'record_id',
        'last_change_date',
        'loyalty_abonement_id',
        'loyalty_certificate_id',
        'good_id',
        'good_title',
        'storage_id',
        'storage_title',
        'client_id',
        'master_id',
        'service_id',
        'service_title',
    ];

    /**
     * Характеристики приведения типов для атрибутов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_id'         => 'integer',
        'company_id'             => 'integer',
        'document_id'            => 'integer',
        'type_id'                => 'integer',
        'operation_unit_type'    => 'integer',
        'amount'                 => 'float',
        'create_date'            => 'datetime',
        'last_change_date'       => 'datetime',
        'cost_per_unit'          => 'float',
        'cost'                   => 'float',
        'discount'               => 'float',
        'record_id'              => 'integer',
        'loyalty_abonement_id'   => 'integer',
        'loyalty_certificate_id' => 'integer',
        'good_id'                => 'integer',
        'storage_id'             => 'integer',
        'client_id'              => 'integer',
        'master_id'              => 'integer',
        'service_id'             => 'integer',
    ];
}
