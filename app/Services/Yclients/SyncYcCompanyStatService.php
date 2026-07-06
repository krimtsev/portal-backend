<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcCompanyStat;

final readonly class SyncYcCompanyStatService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $startDate, ?string $endDate = null): void
    {
        $rawResponse = $this->yclients->analytics()->getCompanyStats(
            $companyId,
            new CompanyStatsFilters(
                date_from: $startDate,
                date_to: $endDate ?? $startDate
            )
        );

        $companyStatsData = $rawResponse['data'] ?? [];

        if (empty($companyStatsData)) {
            return;
        }

        $dto = CompanyStatsResponse::from($companyStatsData);

        YcCompanyStat::updateOrCreate(
            [
                'company_id' => $companyId,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            [
                'income_total'            => $dto->income_total_stats->current_sum,
                'income_goods'            => $dto->income_goods_stats->current_sum,
                'income_services'         => $dto->income_services_stats->current_sum,
                'income_average'          => $dto->income_average_stats->current_sum,
                'income_average_services' => $dto->income_average_services_stats->current_sum,
                'fullness_percent'        => $dto->fullness_stats->current_percent,
                'record_completed'        => $dto->record_stats->current_completed_count,
                'record_pending'          => $dto->record_stats->current_pending_count,
                'record_canceled'         => $dto->record_stats->current_canceled_count,
                'record_total'            => $dto->record_stats->current_total_count,
                'client_new'              => $dto->client_stats->new_count,
                'client_return'           => $dto->client_stats->return_count,
                'client_active'           => $dto->client_stats->active_count,
                'client_lost'             => $dto->client_stats->lost_count,
            ]
        );
    }
}
