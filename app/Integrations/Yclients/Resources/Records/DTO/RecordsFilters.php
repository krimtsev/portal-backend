<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class RecordsFilters extends BaseRequest
{
    public function __construct(
        public readonly ?int $page = 1,
        public readonly ?int $count = 500,
        public readonly ?int $staff_id = null,
        public readonly ?int $client_id = null,
        public readonly ?int $created_user_id = null,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?string $c_start_date = null,
        public readonly ?string $c_end_date = null,
        public readonly ?string $changed_after = null,
        public readonly ?string $changed_before = null,
        public readonly ?int $include_consumables = null,
        public readonly ?int $include_finance_transactions = null,
        public readonly ?bool $with_deleted = null,
    ) {}
}
