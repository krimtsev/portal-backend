<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Единица измерения
 * $id - Идентификатор единицы измерения
 * $title - Название единицы измерения
 */
final class UnitDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $short_title = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'          => ['required', 'integer'],
            'title'       => ['required', 'string'],
            'short_title' => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
