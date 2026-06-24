<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class TransactionsFilters extends BaseRequest
{
    public function __construct(
        public readonly ?int $page = 1,
        public readonly ?int $count = 1000,
        public readonly ?int $account_id = null,
        public readonly ?int $supplier_id = null,
        public readonly ?int $client_id = null,
        public readonly ?int $user_id = null,
        public readonly ?int $master_id = null,
        public readonly ?int $type = null,
        public readonly ?int $real_money = null,
        public readonly ?int $deleted = null,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?int $balance_is = null,
    ) {}
}
