<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class StaffResponse extends BaseResponse
{
    public function __construct(
        public int $id,

        public int $company_id,

        public string $name,

        public string $specialization,

        public int $is_fired,

        public ?string $dismissal_date,

        public float $rating,

        public ?EmployeeDTO $employee,
    ) {}
}
