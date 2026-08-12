<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Jobs\Reports\SendStaffNotificationJob;
use App\Models\Partner\Partner;
use App\Models\Yclients\YcCompanyStaff;
use App\Services\Formatters\CompanyStaffFormatter;

final readonly class SyncYcCompanyStaffService
{
    private const DEFAULT_NAME = 'no_name';

    private const DEFAULT_SPECIALIZATION = 'no_specialization';

    public function __construct(
        private YclientsApi $yclients,
        private CompanyStaffFormatter $telegramFormatter,
    ) {}

    public function sync(int $companyId): void
    {
        $rawResponse = $this->yclients->staff()->getStaff($companyId);
        $companyStaffData = $rawResponse['data'] ?? [];

        if (empty($companyStaffData)) {
            return;
        }

        $upsertData = [];

        $shouldNotify = config('jobs.staff_notifications')
            && config('telegram.channels.staff_updates.chat_id');

        $existingStaff = $shouldNotify
            ? YcCompanyStaff::where('company_id', $companyId)->get()->keyBy('staff_id')
            : null;

        foreach ($companyStaffData as $item) {
            $dto = StaffResponse::from($item);

            $upsertData[] = [
                'company_id'     => $dto->company_id,
                'staff_id'       => $dto->id,
                'name'           => $dto->name ?: self::DEFAULT_NAME,
                'firstname'      => $dto->employee?->firstname ?: null,
                'surname'        => $dto->employee?->surname ?: null,
                'phone'          => $dto->employee?->phone ?: null,
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
                'phone',
                'specialization',
                'fired',
                'dismissal_date',
                'rating',
                'avatar',
                'avatar_big',
            ]
        );

        if (!$shouldNotify || $existingStaff === null) {
            return;
        }

        $partner = Partner::where('yclients_id', $companyId)->first();

        if ($existingStaff->isNotEmpty()) {
            $this->processNotifications(
                $partner,
                $upsertData,
                $existingStaff
            );
        }

    }

    private function processNotifications(Partner $partner, array $staffData, iterable $existingStaff): void
    {
        $branchName = $partner->name;

        foreach ($staffData as $item) {
            $existing = $existingStaff->get($item['staff_id']);

            if (!$existing) {
                $message = $this->telegramFormatter->formatCreated($item, $branchName);

                SendStaffNotificationJob::dispatch(
                    $item['company_id'],
                    $item['staff_id'],
                    $message,
                    $item['avatar']
                );

                continue;
            }

            $changes = $this->detectStaffChanges($existing, $item);

            if (!empty($changes)) {
                $message = $this->telegramFormatter->formatUpdated($item, $branchName, $changes);

                SendStaffNotificationJob::dispatch(
                    $item['company_id'],
                    $item['staff_id'],
                    $message,
                    $item['avatar_big']
                );
            }
        }
    }

    private function detectStaffChanges(YcCompanyStaff $existing, array $newItem): array
    {
        $changes = [];

        if ($existing->name !== $newItem['name']) {
            $changes['name'] = ['old' => $existing->name, 'new' => $newItem['name']];
        }

        if ($existing->specialization !== $newItem['specialization']) {
            $changes['specialization'] = ['old' => $existing->specialization, 'new' => $newItem['specialization']];
        }

        if ($existing->phone !== $newItem['phone']) {
            $changes['phone'] = ['old' => $existing->phone, 'new' => $newItem['phone']];
        }

        if ((bool) $existing->fired !== (bool) $newItem['fired']) {
            $changes['fired'] = ['old' => $existing->fired, 'new' => (bool) $newItem['fired']];
        }

        return $changes;
    }
}
