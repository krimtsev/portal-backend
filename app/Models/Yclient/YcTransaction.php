<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcTransaction extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_transactions';

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

        ];
    }
}
