<?php

namespace App\Integrations\Yclients\Resources\StaffSchedule\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ScheduleSlotDTO extends ValidateResponse
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
    ) {}

    protected static function rules(): array
    {
        return [
            'from' => ['required', 'string'],
            'to'   => ['required', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
