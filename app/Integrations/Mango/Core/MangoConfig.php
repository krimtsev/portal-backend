<?php

namespace App\Integrations\Mango\Core;

readonly class MangoConfig
{
    public function __construct(
        public string $apiKey,
        public string $apiSalt,
    ) {}
}
