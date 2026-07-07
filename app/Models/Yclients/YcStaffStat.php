<?php

declare(strict_types=1);

namespace App\Models\Yclients;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class YcStaffStat extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_staff_stats';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'company_id',
        'start_date',
        'end_date',
        'income_total',
        'income_goods',
        'income_services',
        'income_average',
        'income_average_services',
        'fullness_percent',
        'record_completed',
        'record_pending',
        'record_canceled',
        'record_total',
        'client_new',
        'client_return',
        'client_active',
        'client_lost',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'staff_id'   => 'integer',
            'company_id' => 'integer',
            'start_date' => 'date:Y-m-d',
            'end_date'   => 'date:Y-m-d',

            'income_total'            => 'float',
            'income_goods'            => 'float',
            'income_services'         => 'float',
            'income_average'          => 'float',
            'income_average_services' => 'float',

            'fullness_percent' => 'float',

            'record_completed' => 'integer',
            'record_pending'   => 'integer',
            'record_canceled'  => 'integer',
            'record_total'     => 'integer',

            'client_new'    => 'integer',
            'client_return' => 'integer',
            'client_active' => 'integer',
            'client_lost'   => 'integer',
        ];
    }

    /**
     * Локальный Scope для фильтрации по периоду.
     * YcCompanyDailyStat::forPeriod($start, $end)->get()
     */
    // public function scopeForPeriod(Builder $query, string $startDate, string $endDate): Builder
    // {
    //     return $query->whereBetween('date', [$startDate, $endDate]);
    // }

    public function scopeDailyForPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereNull('end_date')
            ->whereBetween('start_date', [$startDate, $endDate]);
    }

    public function scopeMonthlyForPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereNotNull('end_date')
            ->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Локальный Scope для конкретной компании
     * YcCompanyDailyStat::ForCompany($companyId)->get()
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
