<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcStaffWorkDay extends Model
{
    protected $table = 'yc_staff_work_days';

    protected $fillable = [
        'staff_id',
        'company_id',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
