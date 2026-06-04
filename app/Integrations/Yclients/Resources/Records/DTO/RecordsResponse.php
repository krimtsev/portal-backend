<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class RecordsResponse extends BaseResponse
{
    public function __construct(
        public int $id,
        public int $company_id,
        public int $staff_id,
        public array $services,
        public array $staff,
        public array $client,
        public array $date,
        public array $deleted,

    ) {}

    protected static function getInputMapping(): array
    {
        return [
            'id'         => 'data.id',
            'company_id' => 'data.company_id',
            'staff_id'   => 'data.staff_id',
            'services'   => 'data.services',
            'staff'      => 'data.staff',
            'client'     => 'data.client',
            'date'       => 'data.date',
            'deleted'    => 'data.deleted',
        ];
    }
}
