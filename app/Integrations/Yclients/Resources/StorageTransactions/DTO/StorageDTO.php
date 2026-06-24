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
        public readonly int $id,
        public readonly string $title,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'    => ['required', 'integer'],
            'title' => ['required', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
