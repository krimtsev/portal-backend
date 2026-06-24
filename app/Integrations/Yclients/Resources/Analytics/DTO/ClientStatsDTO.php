<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ClientStatsDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $total_count,
        public readonly int $new_count,
        public readonly int $return_count,
        public readonly int $active_count,
        public readonly int $lost_count,
    ) {}

    protected static function rules(): array
    {
        return [
            'total_count'  => ['required', 'integer'],
            'new_count'    => ['required', 'integer'],
            'return_count' => ['required', 'integer'],
            'active_count' => ['required', 'integer'],
            'lost_count'   => ['required', 'integer'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
