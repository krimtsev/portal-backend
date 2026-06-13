<?php

namespace App\Integrations\Yclients\Resources\Records;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\YclientsException;

class RecordsResource extends ApiResource
{
    /**
     * Получить список записей
     *
     * $filters:
     * $page - Номер страницы
     * $count - Количество записей на странице
     * $staff_id - ID сотрудника
     * $client_id - ID клиента
     * $created_user_id - ID пользователя, создавшего запись
     * $start_date - Дата сеанса начиная с
     * $end_date - Дата сеанса по
     * $c_start_date - Дата создания записи начиная с
     * $c_end_date - Дата создания записи по
     * $changed_after - Дата изменения/создания записи от
     * $changed_before - Дата изменения/создания записи до
     * $include_consumables - Флаг для включения списка расходников
     * $include_finance_transactions - Флаг для включения финансовых транзакций
     * $with_deleted - Включить в выдачу удаленные записи
     *
     * @throws YclientsException
     */
    public function getRecords(int $companyId, ?RecordsFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("records/{$companyId}", $query);
    }
}
