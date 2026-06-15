<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcCompanyDailyStat;

final readonly class SyncYcCompanyDailyStatService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $rawResponse = $this->yclients->analytics()->getCompanyStats(
            $companyId,
            new CompanyStatsFilters(
                date_from: $date,
                date_to: $date
            )
        );

        $companyStatsData = $rawResponse['data'] ?? [];

        if (empty($companyStatsData)) {
            return;
        }

        $dto = CompanyStatsResponse::from($companyStatsData);

        YcCompanyDailyStat::updateOrCreate(
            ['company_id' => $companyId, 'date' => $date],
            [
                'income_total'     => $dto->income_total_stats->current_sum,
                'income_goods'     => $dto->income_goods_stats->current_sum,
                'income_services'  => $dto->income_services_stats->current_sum,
                'fullness_percent' => $dto->fullness_stats->current_percent,
                'record_completed' => $dto->record_stats->current_completed_count,
                'record_pending'   => $dto->record_stats->current_pending_count,
                'record_canceled'  => $dto->record_stats->current_canceled_count,
                'record_total'     => $dto->record_stats->current_total_count,
                'client_new'       => $dto->client_stats->new_count,
                'client_return'    => $dto->client_stats->return_count,
                'client_active'    => $dto->client_stats->active_count,
                'client_lost'      => $dto->client_stats->lost_count,
                'client_total'     => $dto->client_stats->total_count,
            ]
        );
    }
}
