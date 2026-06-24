<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ServiceDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?float $cost = null,
        public readonly ?float $manual_cost = null,
        public readonly ?float $discount = null,
        public readonly ?int $amount = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'          => ['nullable', 'integer'],
            'title'       => ['nullable', 'string'],
            'cost'        => ['nullable', 'numeric'],
            'manual_cost' => ['nullable', 'numeric'],
            'discount'    => ['nullable', 'numeric'],
            'amount'      => ['nullable', 'integer'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
