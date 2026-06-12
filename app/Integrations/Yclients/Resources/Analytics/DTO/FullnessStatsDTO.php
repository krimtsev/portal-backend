<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class FullnessStatsDTO extends ValidateResponse
{
    public function __construct(
        public readonly float $current_percent,
    ) {}

    protected static function rules(): array
    {
        return [
            'current_percent' => ['required', 'numeric'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
