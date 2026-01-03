<?php

namespace App\Models\Certificate;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'certificates';

    protected $fillable = [
        'price',
        'identifier',
        'partner',
        'line',
    ];
}
