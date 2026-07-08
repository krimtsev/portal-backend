<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class StaffResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $company_id,
        public readonly ?string $name,
        public readonly ?string $specialization,
        public readonly int $fired,
        public readonly ?string $dismissal_date,
        public readonly float $rating,
        public readonly string $avatar,
        public readonly string $avatar_big,
        public readonly ?EmployeeDTO $employee,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'             => ['required', 'integer'],
            'company_id'     => ['required', 'integer'],
            'name'           => ['nullable', 'string'],
            'rating'         => ['required', 'numeric'],
            'fired'          => ['required', 'boolean'],
            'specialization' => ['nullable', 'string'],
            'dismissal_date' => ['nullable', 'string'],
            'avatar'         => ['nullable', 'string'],
            'avatar_big'     => ['nullable', 'string'],
            'employee'       => ['nullable', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'employee' => EmployeeDTO::class,
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
