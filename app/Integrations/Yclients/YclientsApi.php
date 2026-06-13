<?php

namespace App\Integrations\Yclients;

use App\Integrations\Yclients\Resources\Analytics\AnalyticsResource;
use App\Integrations\Yclients\Resources\Comments\CommentsResource;
use App\Integrations\Yclients\Resources\Records\RecordsResource;
use App\Integrations\Yclients\Resources\Staff\StaffResource;
use App\Integrations\Yclients\Resources\Transactions\TransactionsResource;

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

    public function comments(): CommentsResource
    {
        return $this->resolveResource(CommentsResource::class);
    }

    public function transactions(): TransactionsResource
    {
        return $this->resolveResource(TransactionsResource::class);
    }

    public function records(): RecordsResource
    {
        return $this->resolveResource(RecordsResource::class);
    }

    public function staff(): StaffResource
    {
        return $this->resolveResource(StaffResource::class);
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
