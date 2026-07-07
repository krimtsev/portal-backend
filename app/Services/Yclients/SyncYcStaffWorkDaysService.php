<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Models\Yclients\YcStaffWorkDay;
use Carbon\Carbon;

final readonly class SyncYcStaffWorkDaysService
{
    public function __construct(
        private YcStaffScheduleService $scheduleService
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $staffData = $this->scheduleService->getActiveStaffWithSources($companyId, $date, $date);

        if (empty($staffData)) {
            return;
        }

        $upsertData = [];
        $formattedDate = Carbon::parse($date)->toDateString();

        foreach ($staffData as $data) {
            $upsertData[] = [
                'staff_id'     => $data['staff_id'],
                'company_id'   => $companyId,
                'date'         => $formattedDate,
                'has_schedule' => $data['has_schedule'],
                'has_records'  => $data['has_records'],
                'has_storage'  => $data['has_storage'],
            ];
        }

        YcStaffWorkDay::upsert(
            $upsertData,
            [
                'staff_id',
                'company_id',
                'date',
            ],
            [

                'has_schedule',
                'has_records',
                'has_storage',
            ],
        );
    }
}
