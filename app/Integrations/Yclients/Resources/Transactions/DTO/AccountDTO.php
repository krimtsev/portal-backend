<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class AccountDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?bool $is_cash = null,
        public readonly ?bool $is_default = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'         => ['nullable', 'integer'],
            'title'      => ['nullable', 'string'],
            'is_cash'    => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
