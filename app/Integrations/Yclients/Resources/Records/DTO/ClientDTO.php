<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class ClientDTO extends ValidateResponse
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $surname = null,
        public readonly ?string $phone = null,
        public readonly ?int $success_visits_count = null,
        public readonly ?int $fail_visits_count = null,
        public readonly ?int $discount = null,
        public readonly ?int $is_new = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'                   => ['nullable', 'integer'],
            'name'                 => ['nullable', 'string'],
            'surname'              => ['nullable', 'string'],
            'phone'                => ['nullable', 'string'],
            'success_visits_count' => ['nullable', 'integer'],
            'fail_visits_count'    => ['nullable', 'integer'],
            'discount'             => ['nullable', 'integer'],
            'is_new'               => ['nullable', 'boolean'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
