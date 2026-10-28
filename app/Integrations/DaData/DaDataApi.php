<?php

namespace App\Integrations\DaData;

use App\Integrations\DaData\Core\DaDataClient;
use App\Integrations\DaData\Resources\Address\AddressResource;
use App\Integrations\DaData\Resources\Bank\BankResource;
use App\Integrations\DaData\Resources\Party\PartyResource;

final class DaDataApi
{
    /** @var array<string, object> */
    protected array $resources = [];

    public function __construct(
        private readonly DaDataClient $client,
    ) {}

    public function bank(): BankResource
    {
        return $this->resolveResource(BankResource::class);
    }

    public function address(): AddressResource
    {
        return $this->resolveResource(AddressResource::class);
    }

    public function party(): PartyResource
    {
        return $this->resolveResource(PartyResource::class);
    }

    protected function resolveResource(string $class)
    {
        return $this->resources[$class] ??= new $class($this->client);
    }
}
