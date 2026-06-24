<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\StorageTransactions\DTO\StorageTransactionsFilters;
use App\Integrations\Yclients\YclientsException;

final class StorageTransactionsResource extends ApiResource
{
    /**
     * Поиск товарных транзакций
     *
     * $filters:
     * $page - Номер страницы
     * $count - Количество клиентов на странице
     * $start_date - дата начала периода
     * $end_date - дата окончания периода
     * $document_id - идентификатор документа
     * $changed_after - Фильтрация товарных транзакций, измененных/созданных начиная с конкретной даты и времени
     * $changed_before - Фильтрация товарных транзакций, измененных/созданных до конкретной даты и времени
     *
     * @throws YclientsException
     */
    public function getStorageTransactions(int $companyId, ?StorageTransactionsFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("storages/transactions/{$companyId}", $query);
    }
}
