<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

class ClientDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $surname,
        public ?string $phone,
        public ?int $success_visits_count,
        public ?int $fail_visits_count,
        public ?int $discount,
        public ?int $is_new,
    ) {}
}
