<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

class RecordsFilters extends BaseRequest
{
    public function __construct(
        public ?int $page = null,
        public ?int $count = null,
        public ?int $staff_id = null,
        public ?int $client_id = null,
        public ?int $created_user_id = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?string $c_start_date = null,
        public ?string $c_end_date = null,
        public ?string $changed_after = null,
        public ?string $changed_before = null,
        public ?int $include_consumables = null,
        public ?int $include_finance_transactions = null,
        public ?bool $with_deleted = null,
    ) {}
}
