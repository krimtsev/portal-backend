<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class RecordsResponse extends ValidateResponse
{
    /**
     * @param  ServiceDTO[]  $services
     */
    public function __construct(
        public readonly int $id,
        public readonly int $company_id,
        public readonly int $staff_id,
        public readonly int $visit_id,
        public readonly string $datetime,
        public readonly array $services,
        public readonly ?ClientDTO $client,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'         => ['required', 'integer'],
            'company_id' => ['required', 'integer'],
            'staff_id'   => ['required', 'integer'],
            'visit_id'   => ['required', 'integer'],
            'datetime'   => ['required', 'string'],
            'services'   => ['nullable', 'array'],
            'client'     => ['nullable', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'services' => [ServiceDTO::class],
            'client'   => ClientDTO::class,
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
