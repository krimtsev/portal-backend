<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

class MasterDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $avatar,
    ) {}
}
