<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class IncomeStatsDTO extends ValidateResponse
{
    public function __construct(
        public readonly float $current_sum,
    ) {}

    protected static function rules(): array
    {
        return [
            'current_sum' => ['required', 'numeric'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
