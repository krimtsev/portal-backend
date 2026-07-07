<?php

declare(strict_types=1);

namespace App\Models\Yclients;

use Illuminate\Database\Eloquent\Model;

class YcTariff extends Model
{
    protected $table = 'yc_tariffs';

    protected $fillable = [
        'service_id',
        'title',
        'cost',
        'start_date',
        'end_date',
        'disabled',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'cost'       => 'decimal:2',
        'start_date' => 'date',
        'end_date'   => 'date',
        'disabled'   => 'boolean',
    ];
}
