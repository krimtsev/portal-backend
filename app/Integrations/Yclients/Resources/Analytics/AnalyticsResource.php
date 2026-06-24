<?php

namespace App\Integrations\Yclients\Resources\Analytics;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\YclientsException;

final class AnalyticsResource extends ApiResource
{
    /**
     * Получить основные показатели компании
     *
     * $filters:
     * $date_from - Дата начала анализируемого периода
     * $date_to - Дата окончания анализируемого периода (включается в отчет)
     * $staff_id - Идентификатор сотрудника компании
     * $position_id - Идентификатор должности компании для анализа работы всех сотрудников
     * $user_id - Идентификатор пользователя компании
     *
     * @throws YclientsException
     */
    public function getCompanyStats(int $companyId, ?CompanyStatsFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("company/{$companyId}/analytics/overall", $query);
    }
}
