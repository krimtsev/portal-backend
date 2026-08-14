<?php

namespace App\Integrations\Mango\Resources\CallsStats\DTO;

use App\Integrations\Mango\Core\BaseRequest;

final class CallsStatsRequestFilters extends BaseRequest
{
    public function __construct(
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly int $limit = 1000,
        public readonly int $offset = 0,
        public readonly int|array|null $context_type = null,
        public readonly ?array $user_ids = null,
        public readonly ?array $group_ids = null,
        public readonly ?int $context_status = null,
        public readonly ?int $recall_status = null,
        public readonly ?string $search_string = null,
        public readonly ?int $ext_params = null,
        public readonly ?array $ext_fields = null,
    ) {}
}
