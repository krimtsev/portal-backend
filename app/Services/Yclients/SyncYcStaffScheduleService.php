<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\StaffSchedule\DTO\StaffScheduleFilters;
use App\Integrations\Yclients\YclientsApi;

final readonly class SyncYcStaffScheduleService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function getActiveStaffIds(int $companyId, string $date): array
    {
        $rawResponse = $this->yclients->staffSchedule()->getStaffSchedule(
            $companyId,
            new StaffScheduleFilters(
                start_date: $date,
                end_date: $date
            )
        );

        $staffScheduleData = $rawResponse['data'] ?? [];

        if (empty($staffScheduleData)) {
            return [];
        }

        return collect($staffScheduleData)
            ->pluck('staff_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
