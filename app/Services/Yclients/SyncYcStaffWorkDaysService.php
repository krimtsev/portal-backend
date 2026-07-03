<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Models\Yclient\YcStaffWorkDay;
use Carbon\Carbon;

final readonly class SyncYcStaffWorkDaysService
{
    public function __construct(
        private YcStaffScheduleService $scheduleService
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $staffIds = $this->scheduleService->getActiveStaffIds($companyId, $date);

        if (empty($staffIds)) {
            return;
        }

        $upsertData = [];
        $formattedDate = Carbon::parse($date)->toDateString();

        foreach ($staffIds as $staffId) {
            $upsertData[] = [
                'staff_id'   => $staffId,
                'company_id' => $companyId,
                'date'       => $formattedDate,
            ];
        }

        YcStaffWorkDay::upsert(
            $upsertData,
            [
                'staff_id',
                'company_id',
                'date',
            ],
        );
    }
}
