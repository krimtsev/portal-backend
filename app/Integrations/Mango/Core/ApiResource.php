<?php

namespace App\Integrations\Mango\Core;

use App\Integrations\Mango\MangoClient;

abstract class ApiResource
{
    public function __construct(
        protected MangoClient $client
    ) {}
}
