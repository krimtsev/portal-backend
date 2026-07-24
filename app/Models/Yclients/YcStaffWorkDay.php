<?php

declare(strict_types=1);

namespace App\Models\Yclients;

use Illuminate\Database\Eloquent\Model;

final class YcStaffWorkDay extends Model
{
    protected $table = 'yc_staff_work_days';

    protected $fillable = [
        'staff_id',
        'company_id',
        'date',
        'has_schedule',
        'has_records',
        'has_storage',
    ];

    protected $casts = [
        'date'         => 'date',
        'has_schedule' => 'boolean',
        'has_records'  => 'boolean',
        'has_storage'  => 'boolean',
    ];
}
