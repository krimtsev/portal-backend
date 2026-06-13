<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcCompanyStaff extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_company_staff';

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
        'is_fired',
        'dismissal_date',
        'rating',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_fired'       => 'boolean',
            'dismissal_date' => 'date:Y-m-d',
            'rating'         => 'decimal:2',
        ];
    }
}
