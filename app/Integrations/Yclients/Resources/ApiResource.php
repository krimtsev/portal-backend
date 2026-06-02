<?php

namespace App\Integrations\Yclients\Resources;

use App\Integrations\Yclients\YclientsClient;

abstract class ApiResource
{
    public function __construct(
        protected readonly YclientsClient $client
    ) {}
}
