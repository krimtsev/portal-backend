<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class StaffResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $company_id,
        public readonly string $name,
        public readonly string $specialization,
        public readonly int $fired,
        public readonly ?string $dismissal_date,
        public readonly float $rating,
        public readonly ?EmployeeDTO $employee,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'             => ['required', 'integer'],
            'company_id'     => ['required', 'integer'],
            'name'           => ['required', 'string'],
            'rating'         => ['required', 'numeric'],
            'fired'          => ['required', 'boolean'],
            'specialization' => ['required', 'string'],
            'dismissal_date' => ['nullable', 'string'],
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
