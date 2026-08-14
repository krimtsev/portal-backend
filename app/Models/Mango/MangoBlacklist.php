<?php

namespace App\Models\Mango;

use Illuminate\Database\Eloquent\Model;

class MangoBlacklist extends Model
{
    protected $table = 'mango_blacklist';

    protected $fillable = [
        'number_id',
        'number',
        'number_type',
        'comment',
    ];

    protected $casts = [
        'number_id'   => 'integer',
        'number'      => 'string',
        'number_type' => 'string',
        'comment'     => 'string',
    ];
}
