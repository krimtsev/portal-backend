<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\StaffSchedule\DTO\StaffScheduleFilters;
use App\Integrations\Yclients\Support\YclientsDataResolver;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclients\YcStaffWorkDay;

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

        return array_column($this->getActiveStaffWithSources($companyId, $startDate, $endDate), 'staff_id');
    }

    /**
     * Собирает уникальные ID с указанием источников их происхождения.
     */
    public function getActiveStaffWithSources(int $companyId, string $startDate, string $endDate): array
    {
        $staffIds = $this->getScheduleStaffIds($companyId, $startDate, $endDate);

        return array_map(static fn (int $id): array => [
            'staff_id'     => $id,
            'has_schedule' => true,
        ], $staffIds);
    }

    /**
     * Получает ID сотрудников из расписания YClients.
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

        $data = YclientsDataResolver::extractData($rawResponse);

        return collect($data)
            ->pluck('staff_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
