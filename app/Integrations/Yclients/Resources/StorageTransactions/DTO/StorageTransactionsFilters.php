<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class StorageTransactionsFilters extends BaseRequest
{
    public function __construct(
        public readonly ?int $page = 1,
        public readonly ?int $count = 1000,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?int $document_id = null,
        public readonly ?string $changed_after = null,
        public readonly ?string $changed_before = null,
    ) {}
}
