<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class EmployeeDTO extends ValidateResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $firstname,
        public ?string $surname,
        public ?string $patronymic,
        public ?string $date_admission,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'             => ['required', 'integer'],
            'name'           => ['required', 'string'],
            'phone'          => ['nullable', 'string'],
            'firstname'      => ['nullable', 'string'],
            'surname'        => ['nullable', 'string'],
            'patronymic'     => ['nullable', 'string'],
            'date_admission' => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
