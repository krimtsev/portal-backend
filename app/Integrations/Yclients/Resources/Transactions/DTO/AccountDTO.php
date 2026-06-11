<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

class AccountDTO
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?bool $is_cash,
        public ?bool $is_default,
    ) {}
}
