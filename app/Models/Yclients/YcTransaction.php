<?php

declare(strict_types=1);

namespace App\Models\Yclients;

use Illuminate\Database\Eloquent\Model;

final class YcTransaction extends Model
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
        'master_id',
        'document_id',
        'record_id',
        'visit_id',
        'account_id',
        'account_title',
        'client_id',
        'amount',
        'date',
        'sold_item_id',
        'sold_item_type',
        'expense_id',
        'expense_title',
        'expense_type',
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
