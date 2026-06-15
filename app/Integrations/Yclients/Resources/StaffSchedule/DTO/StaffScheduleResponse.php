<?php

namespace App\Integrations\Yclients\Resources\StaffSchedule\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class StaffScheduleResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $staff_id,
        public readonly string $date,
        public readonly array $slots,
    ) {}

    protected static function rules(): array
    {
        return [
            'staff_id' => ['required', 'integer'],
            'date'     => ['required', 'date_format:Y-m-d'],
            'slots'    => ['required', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'slots' => [ScheduleSlotDTO::class],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
