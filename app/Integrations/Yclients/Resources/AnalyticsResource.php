<?php

namespace App\Integrations\Yclients\Resources;

use App\Integrations\Yclients\YclientsException;

class AnalyticsResource extends ApiResource
{
    /**
     * Получить основные показатели компании
     * @throws YclientsException
     */
    public function getCompanyStats(
        int $companyId,
        string $dateFrom,
        string $dateTo,
        ?int $staffId = null,
    ): array {
        return $this->client->get("company/{$companyId}/analytics/overall", [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'staff_id'  => $staffId,
        ]);
    }
}
