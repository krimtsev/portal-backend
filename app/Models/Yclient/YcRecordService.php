<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcRecordService extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_record_services';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'company_id',
        'title',
        'cost',
        'manual_cost',
        'analytics_cost',
        'discount',
        'amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost'        => 'float',
            'manual_cost' => 'float',
        ];
    }
}
