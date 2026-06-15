<?php

namespace App\Integrations\Yclients\Resources\StaffSchedule;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\StaffSchedule\DTO\StaffScheduleFilters;
use App\Integrations\Yclients\YclientsException;

class StaffScheduleResource extends ApiResource
{
    /**
     * Получение графиков работы сотрудников
     *
     * @throws YclientsException
     */
    public function getStaffSchedule(int $companyId, ?StaffScheduleFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("company/{$companyId}/staff/schedule", $query);
    }
}
