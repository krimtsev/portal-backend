<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclients\YcCompanyStaff;

final readonly class SyncYcCompanyStaffService
{
    private const DEFAULT_NAME = "no_name";
    private const DEFAULT_SPECIALIZATION = "no_specialization";

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
                'name'           => $dto->name ?: self::DEFAULT_NAME,
                'firstname'      => $dto->employee?->firstname ?: null,
                'surname'        => $dto->employee?->surname ?: null,
                'specialization' => $dto->specialization ?: self::DEFAULT_SPECIALIZATION,
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
