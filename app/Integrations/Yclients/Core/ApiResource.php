<?php

namespace App\Integrations\Yclients\Core;

use App\Integrations\Yclients\YclientsClient;

abstract class ApiResource
{
    public function __construct(
        protected readonly YclientsClient $client
    ) {}
}
