<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class StaffResponse extends BaseResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $specialization,
        public int $fired,
        public string|null $dismissal_date,
        public float $rating,
        public array $employee,

    ) {}

    protected static function getInputMapping(): array
    {
        return [
            'id'             => 'data.id',
            'name'           => 'data.name',
            'specialization' => 'data.specialization',
            'fired'          => 'data.fired',
            'dismissal_date' => 'data.dismissal_date',
            'employee'       => 'data.employee',
        ];
    }
}
