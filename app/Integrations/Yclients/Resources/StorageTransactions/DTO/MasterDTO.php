<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Сотрудник
 * $id - Идентификатор сотрудника
 * $title - Имя сотрудника
 */
final class MasterDTO extends ValidateResponse
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
