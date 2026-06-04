<?php

namespace App\Integrations\Yclients\Resources\Staff;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\YclientsException;

class StaffResource extends ApiResource
{
    /**
     * Получить список сотрудников
     *
     *
     * @throws YclientsException
     */
    public function getStaff(int $companyId, ?int $staffId): array
    {
        return $this->client->get("company/{$companyId}/staff");
    }

    /**
     * Получить список сотрудников
     *
     *
     * @throws YclientsException
     */
    public function getStaffById(int $companyId, int $staffId): array
    {
        return $this->client->get("company/{$companyId}/staff/{$staffId}");
    }
}
