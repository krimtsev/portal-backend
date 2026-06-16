<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_transactions';

    protected $primaryKey = 'transaction_id';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'company_id',
        'staff_id',
        'record_id',
        'visit_id',
        'document_id',
        'amount',
        'sold_item_type',
        'expense_id',
        'expense_title',
        'expense_type',
        'date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }
}
