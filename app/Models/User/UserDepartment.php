<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserDepartment extends Pivot
{
    protected $table = 'user_departments';

    protected $primaryKey = 'department_id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'department_id',
    ];
}
