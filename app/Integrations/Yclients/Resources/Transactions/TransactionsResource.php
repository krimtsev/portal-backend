<?php

namespace App\Integrations\Yclients\Resources\Transactions;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\YclientsException;

final class TransactionsResource extends ApiResource
{
    /**
     * Получить транзакции
     *
     * $filters:
     * $page - Номер страницы
     * $count - Количество клиентов на странице
     * $account_id - ID кассы
     * $supplier_id - ID контрагента
     * $client_id - ID клиента
     * $user_id - ID пользователя
     * $master_id - ID сотрудника
     * $type - тип транзакции
     * $real_money - транзакция реальными деньгами
     * $deleted - была ли удалена транзакция
     * $start_date - дата начала периода
     * $end_date - дата окончания периода
     * $balance_is - 0 - любой баланс, 1 - положительный, 2 - отрицательный
     * $document_id - идентификатор документа
     *
     * @throws YclientsException
     */
    public function getTransactions(int $companyId, ?TransactionsFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("transactions/{$companyId}", $query);
    }
}
