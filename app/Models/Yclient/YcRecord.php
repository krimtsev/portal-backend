<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcRecord extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_records';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'record_id',
        'company_id',
        'staff_id',
        'visit_id',
        'client_id',
        'client_name',
        'client_phone',
        'client_success_visits',
        'client_fail_visits',
        'datetime',

        /**
         * TODO: проверить необходимость таких колонок.
         * может быть не актуально из за наборов.
         * или подумать, считать суммы с учетом наборов.
         */
        'total_cost',
        'total_manual_cost',
        'total_analytics_cost',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'datetime' => 'datetime',
        ];
    }
}
