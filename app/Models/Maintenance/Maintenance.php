<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenance';

    protected $fillable = [
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
