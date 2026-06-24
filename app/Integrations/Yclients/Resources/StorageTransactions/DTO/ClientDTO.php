<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Клиент
 * $id - Идентификатор клиента
 * $name - Имя клиента
 * $phone - Телефон клиента
 */
final class ClientDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $phone = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'    => ['required', 'integer'],
            'name'  => ['required', 'string'],
            'phone' => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
