<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

class ExpenseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?int $type,
    ) {}
}
