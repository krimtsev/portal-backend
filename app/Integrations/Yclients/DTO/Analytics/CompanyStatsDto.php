<?php

namespace App\Integrations\Yclients\DTO\Analytics;

use App\Integrations\Yclients\DTO\BaseDto;

class CompanyStatsDto extends BaseDto
{
    public function __construct(
        public float $income_total,
        public float $income_services,
        public float $income_goods,
        public float $fullness_percent,
        public int $record_completed,
        public int $record_pending,
        public int $record_canceled,
        public int $record_total,
        public int $client_total,
        public int $client_new,
        public int $client_return,
        public int $client_active,
        public int $client_lost
    ) {}

    protected static function getInputMapping(): array
    {
        return [
            'income_total'      => 'data.income_total_stats.current_sum',
            'income_services'   => 'data.income_services_stats.current_sum',
            'income_goods'      => 'data.income_goods_stats.current_sum',
            'fullness_percent'  => 'data.fullness_stats.current_percent',
            'record_completed'  => 'data.record_stats.current_completed_count',
            'record_pending'    => 'data.record_stats.current_pending_count',
            'record_canceled'   => 'data.record_stats.current_canceled_count',
            'record_total'      => 'data.record_stats.current_total_count',
            'client_total'      => 'data.client_stats.total_count',
            'client_new'        => 'data.client_stats.new_count',
            'client_return'     => 'data.client_stats.return_count',
            'client_active'     => 'data.client_stats.active_count',
            'client_lost'       => 'data.client_stats.lost_count',
        ];
    }
}
