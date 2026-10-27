<?php

namespace App\Integrations\DaData;

use App\Integrations\DaData\Core\DaDataClient;
use App\Integrations\DaData\Resources\Bank\BankResource;

final class DaDataApi
{
    /** @var array<string, object> */
    protected array $resources = [];

    public function __construct(
        private readonly DadataClient $client,
    ) {}

    public function bank(): BankResource
    {
        return $this->resolveResource(BankResource::class);
    }

    protected function resolveResource(string $class)
    {
        return $this->resources[$class] ??= new $class($this->client);
    }
}
