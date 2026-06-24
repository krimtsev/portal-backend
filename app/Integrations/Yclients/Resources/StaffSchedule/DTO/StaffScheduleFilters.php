<?php

namespace App\Integrations\Yclients\Resources\StaffSchedule\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class StaffScheduleFilters extends BaseRequest
{
    public function __construct(
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?array $staff_ids = null,
        public readonly ?array $include = null,
    ) {}
}
