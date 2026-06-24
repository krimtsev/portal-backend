<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcCompanyStaff;

final readonly class SyncYcCompanyStaffService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId): void
    {
        $rawResponse = $this->yclients->staff()->getStaff($companyId);
        $companyStaffData = $rawResponse['data'] ?? [];

        if (empty($companyStaffData)) {
            return;
        }

        $upsertData = [];

        foreach ($companyStaffData as $item) {
            $dto = StaffResponse::from($item);

            $upsertData[] = [
                'company_id'     => $dto->company_id,
                'staff_id'       => $dto->id,
                'name'           => $dto->name,
                'firstname'      => $dto->employee?->firstname ?: null,
                'surname'        => $dto->employee?->surname ?: null,
                'specialization' => $dto->specialization,
                'fired'          => $dto->fired,
                'dismissal_date' => $dto->dismissal_date,
                'rating'         => $dto->rating,
                'avatar'         => $dto->avatar,
                'avatar_big'     => $dto->avatar_big,
            ];
        }

        YcCompanyStaff::upsert(
            $upsertData,
            [
                'company_id',
                'staff_id',
            ],
            [
                'name',
                'firstname',
                'surname',
                'specialization',
                'fired',
                'dismissal_date',
                'rating',
                'avatar',
                'avatar_big',
            ]
        );
    }
}
