<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YcRecord extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_records';

    protected $primaryKey = 'record_id';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'record_id',
        'company_id',
        'staff_id',
        'visit_id',
        'client_id',
        'client_name',
        'client_phone',
        'client_success_visits',
        'client_fail_visits',
        'datetime',
        'visit_attendance',
        'attendance',
        'confirmed',
        'length',

        'total_cost',
        'total_manual_cost',
        'total_tariff_cost',
        'total_base_tariff_cost',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'datetime'               => 'datetime',
            'total_cost'             => 'float',
            'total_manual_cost'      => 'float',
            'total_tariff_cost'      => 'float',
            'total_base_tariff_cost' => 'float',
            'discount'               => 'float',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(YcRecordService::class, 'record_id', 'record_id');
    }
}
