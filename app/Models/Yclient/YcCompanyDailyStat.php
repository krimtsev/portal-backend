<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class YcCompanyDailyStat extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_company_daily_stats';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'date',
        'income_total',
        'income_goods',
        'income_services',
        'fullness_percent',
        'record_completed',
        'record_pending',
        'record_canceled',
        'record_total',
        'client_new',
        'client_return',
        'client_active',
        'client_lost',
        'client_total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'date'       => 'date:Y-m-d',

            'income_total'    => 'float',
            'income_goods'    => 'float',
            'income_services' => 'float',

            'fullness_percent' => 'float',

            'record_completed' => 'integer',
            'record_pending'   => 'integer',
            'record_canceled'  => 'integer',
            'record_total'     => 'integer',

            'client_new'    => 'integer',
            'client_return' => 'integer',
            'client_active' => 'integer',
            'client_lost'   => 'integer',
            'client_total'  => 'integer',
        ];
    }

    /**
     * Локальный Scope для фильтрации по периоду.
     * YcCompanyDailyStat::forPeriod($start, $end)->get()
     */
    public function scopeForPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
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
