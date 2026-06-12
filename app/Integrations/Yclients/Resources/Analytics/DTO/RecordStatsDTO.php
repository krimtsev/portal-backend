<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class RecordStatsDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $current_completed_count,
        public readonly int $current_pending_count,
        public readonly int $current_canceled_count,
        public readonly int $current_total_count,
    ) {}

    protected static function rules(): array
    {
        return [
            'current_completed_count' => ['required', 'integer'],
            'current_pending_count'   => ['required', 'integer'],
            'current_canceled_count'  => ['required', 'integer'],
            'current_total_count'     => ['required', 'integer'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
