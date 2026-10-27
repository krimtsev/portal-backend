<?php

namespace App\Integrations\DaData\Core;

readonly class DaDataConfig
{
    public function __construct(
        public string $apiKey,
        public string $apiSecretKey,
    ) {}
}
