<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Услуга
 * $id - Идентификатор услуги
 * $title - Название услуги
 */
final class ServiceDTO extends ValidateResponse
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
