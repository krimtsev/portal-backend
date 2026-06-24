<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Поставщик
 * $id - Идентификатор поставщика
 * $title - Название поставщика
 */
final class SupplierDTO extends ValidateResponse
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
