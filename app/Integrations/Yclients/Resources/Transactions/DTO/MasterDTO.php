<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class MasterDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $avatar = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'     => ['nullable', 'integer'],
            'name'   => ['nullable', 'string'],
            'avatar' => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
