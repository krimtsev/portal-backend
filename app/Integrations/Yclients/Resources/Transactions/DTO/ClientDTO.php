<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

class ClientDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $surname,
        public ?string $patronymic,
        public ?string $phone,
    ) {}
}
