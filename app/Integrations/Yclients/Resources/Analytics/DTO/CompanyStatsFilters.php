<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

class CompanyStatsFilters extends BaseRequest
{
    public function __construct(
        public string $date_from,
        public string $date_to,
        public ?int $staffId = null,
        public ?int $position_id = null,
        public ?int $user_id = null,
    ) {}
}
