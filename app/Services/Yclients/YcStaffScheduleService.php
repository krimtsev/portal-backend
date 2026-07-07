<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\StaffSchedule\DTO\StaffScheduleFilters;
use App\Integrations\Yclients\Resources\StorageTransactions\DTO\StorageTransactionsFilters;
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
        $staffMap = [];

        $this->hydrateStaffSource(
            $this->getScheduleStaffIds($companyId, $startDate, $endDate),
            'has_schedule',
            $staffMap
        );

        $this->hydrateStaffSource(
            $this->getRecordStaffIds($companyId, $startDate, $endDate),
            'has_records',
            $staffMap
        );

        $this->hydrateStaffSource(
            $this->getStorageStaffIds($companyId, $startDate, $endDate),
            'has_storage',
            $staffMap
        );

        return array_values($staffMap);
    }

    /**
     * Наполняет карту сотрудников признаком присутствия в определенном источнике.
     *
     * @param  array<int>  $ids
     * @param  array<int, array>  $staffMap
     */
    private function hydrateStaffSource(array $ids, string $flag, array &$staffMap): void
    {
        foreach ($ids as $id) {
            $staffMap[$id] ??= [
                'staff_id'     => $id,
                'has_schedule' => false,
                'has_records'  => false,
                'has_storage'  => false,
            ];

            $staffMap[$id][$flag] = true;
        }
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
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Получает ID сотрудников из складских транзакций.
     */
    public function getStorageStaffIds(int $companyId, string $startDate, string $endDate): array
    {
        $rawResponse = $this->yclients->storageTransactions()->getStorageTransactions(
            $companyId,
            new StorageTransactionsFilters(
                start_date: $startDate,
                end_date: $endDate
            )
        );

        return collect($rawResponse['data'] ?? [])
            ->pluck('master.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
