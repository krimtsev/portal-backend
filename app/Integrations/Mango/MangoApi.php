<?php

namespace App\Integrations\Mango;

use App\Integrations\Mango\Resources\Bwlists\BwlistsResource;
use App\Integrations\Mango\Resources\CallsStats\CallsStatsResource;

final class MangoApi
{
    /** @var array<string, object> */
    protected array $resources = [];

    public function __construct(
        private readonly MangoClient $client,
    ) {}

    public function bwlists(): BwlistsResource
    {
        return $this->resolveResource(BwlistsResource::class);
    }

    public function callsStats(): CallsStatsResource
    {
        return $this->resolveResource(CallsStatsResource::class);
    }

    protected function resolveResource(string $class)
    {
        return $this->resources[$class] ??= new $class($this->client);
    }
}
