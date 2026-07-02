<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'record_id',
        'service_id',
        'company_id',
        'title',
        'cost',
        'manual_cost',
        'tariff_cost',
        'base_tariff_cost',
        'discount',
        'amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost'             => 'float',
            'manual_cost'      => 'float',
            'tariff_cost'      => 'float',
            'base_tariff_cost' => 'float',
            'discount'         => 'float',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(YcRecord::class, 'record_id', 'record_id');
    }
}
