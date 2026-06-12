<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class CompanyStatsFilters extends BaseRequest
{
    public function __construct(
        public readonly string $date_from,
        public readonly string $date_to,
        public readonly ?int $staffId = null,
        public readonly ?int $position_id = null,
        public readonly ?int $user_id = null,
    ) {}
}
