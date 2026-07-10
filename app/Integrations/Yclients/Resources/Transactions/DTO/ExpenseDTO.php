<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ExpenseDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $title,
        public readonly ?int $type,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'    => ['sometimes', 'nullable'],
            'title' => ['sometimes', 'nullable'],
            'type'  => ['sometimes', 'nullable'],
        ];
    }

    protected static function build(array $validated): static
    {
        $clean = array_map(fn($value) => $value === '' ? null : $value, $validated);

        return new self(
            id:    isset($clean['id'])    ? (int) $clean['id'] : null,
            title: isset($clean['title']) ? (string) $clean['title'] : null,
            type:  isset($clean['type'])  ? (int) $clean['type'] : null,
        );
    }
}
