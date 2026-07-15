<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Склад
 * $id - Идентификатор склада
 * $title - Название склада
 */
final class StorageDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $title,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'    => ['sometimes', 'nullable'],
            'title' => ['sometimes', 'nullable'],
        ];
    }

    protected static function build(array $validated): static
    {
        $clean = array_map(fn ($value) => $value === '' ? null : $value, $validated);

        return new self(
            id: isset($clean['id']) ? (int) $clean['id'] : null,
            title: isset($clean['title']) ? (string) $clean['title'] : null,
        );
    }
}
