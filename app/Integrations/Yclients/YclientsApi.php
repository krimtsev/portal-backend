<?php

namespace App\Integrations\Yclients;

use App\Integrations\Yclients\Resources\AnalyticsResource;

class YclientsApi
{
    /** @var array<string, object> */
    protected array $resources = [];

    public function __construct(
        protected readonly YclientsClient $client
    ) {}

    public function analytics(): AnalyticsResource
    {
        return $this->resolveResource(AnalyticsResource::class);
    }

    /**
     * Создает объект ресурса только 1 раз при первом обращении.
     *
     * @template T
     *
     * @param  class-string<T>  $class
     * @return T
     */
    protected function resolveResource(string $class)
    {
        return $this->resources[$class] ??= new $class($this->client);
    }
}
