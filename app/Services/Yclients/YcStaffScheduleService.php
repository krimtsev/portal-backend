<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\StaffSchedule\DTO\StaffScheduleFilters;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcStaffWorkDay;

final readonly class YcStaffScheduleService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function getStaffIdsForDate(int $companyId, string $startDate, string $endDate): array
    {
        $staffIds = YcStaffWorkDay::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('staff_id')
            ->unique()
            ->values()
            ->toArray();

        if (!empty($staffIds)) {
            return $staffIds;
        }

        return $this->getActiveStaffIds($companyId, $startDate, $endDate);
    }

    /**
     * Собирает уникальные ID из расписания и записей.
     */
    public function getActiveStaffIds(int $companyId, string $startDate, string $endDate): array
    {
        $scheduleStaffIds = collect($this->getScheduleStaffIds($companyId, $startDate, $endDate));
        $recordsStaffIds = collect($this->getRecordStaffIds($companyId, $startDate, $endDate));

        return $scheduleStaffIds
            ->merge($recordsStaffIds)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Получает ID сотрудников из расписания.
     */
    public function getScheduleStaffIds(int $companyId, string $startDate, string $endDate): array
    {
        $rawResponse = $this->yclients->staffSchedule()->getStaffSchedule(
            $companyId,
            new StaffScheduleFilters(
                start_date: $startDate,
                end_date: $endDate
            )
        );

        return collect($rawResponse['data'] ?? [])
            ->pluck('staff_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Получает ID сотрудников из записей (records).
     */
    public function getRecordStaffIds(int $companyId, string $startDate, string $endDate): array
    {
        $rawResponse = $this->yclients->records()->getRecords(
            $companyId,
            new RecordsFilters(
                start_date: $startDate,
                end_date: $endDate
            )
        );

        return collect($rawResponse['data'] ?? [])
            ->pluck('staff_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
