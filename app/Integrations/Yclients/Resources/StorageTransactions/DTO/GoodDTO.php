<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Товар
 * $id - Идентификатор товара
 * $title - Название товара
 */
final class GoodDTO extends ValidateResponse
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
