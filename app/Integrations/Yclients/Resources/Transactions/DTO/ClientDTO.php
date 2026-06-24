<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ClientDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $surname = null,
        public readonly ?string $patronymic = null,
        public readonly ?string $phone = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'         => ['nullable', 'integer'],
            'name'       => ['nullable', 'string'],
            'surname'    => ['nullable', 'string'],
            'patronymic' => ['nullable', 'string'],
            'phone'      => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
