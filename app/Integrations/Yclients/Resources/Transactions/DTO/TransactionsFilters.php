<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

class TransactionsFilters extends BaseRequest
{
    public function __construct(
        public ?int $page = null,
        public ?int $count = null,
        public ?int $account_id = null,
        public ?int $supplier_id = null,
        public ?int $client_id = null,
        public ?int $user_id = null,
        public ?int $master_id = null,
        public ?int $type = null,
        public ?int $real_money = null,
        public ?int $deleted = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?int $balance_is = null,
        public ?int $document_id = null,
    ) {}
}
