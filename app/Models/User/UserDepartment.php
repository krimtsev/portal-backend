<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class UserDepartment extends Pivot
{
    protected $table = 'user_departments';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'department_id',
    ];
}
