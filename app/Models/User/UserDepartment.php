<?php


namespace App\Models\User;

use App\Enums\Department;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserDepartment extends Pivot
{
    protected $table = 'user_departments';

    public $timestamps = false;

    protected $primaryKey = 'department_slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'department_slug',
    ];

    protected function casts(): array
    {
        return [
            'department_slug' => Department::class,
        ];
    }
}
