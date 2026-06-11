<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

class ServiceDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public float $cost,
        public float $manual_cost,
        public int $discount,
        public int $amount,
    ) {}
}
