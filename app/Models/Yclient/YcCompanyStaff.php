<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcCompanyStaff extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_company_staff';

    protected $primaryKey = 'staff_id';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'company_id',
        'name',
        'firstname',
        'surname',
        'specialization',
        'fired',
        'dismissal_date',
        'rating',
        'avatar',
        'avatar_big',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fired'          => 'boolean',
            'dismissal_date' => 'date:Y-m-d',
            'rating'         => 'decimal:2',
        ];
    }
}
