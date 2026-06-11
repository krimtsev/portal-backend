<?php

namespace App\Integrations\Yclients\Resources\Staff\DTO;

final readonly class EmployeeDTO
{
    public function __construct(
        public ?int $id,
        public ?string $phone,
        public ?string $name,
        public ?string $firstname,
        public ?string $surname,
        public ?string $patronymic,
        public ?string $date_admission,
    ) {}
}
