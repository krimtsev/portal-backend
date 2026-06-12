<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ExpenseDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly int $type,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'    => ['required', 'integer'],
            'title' => ['required', 'string'],
            'type'  => ['required', 'integer'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
