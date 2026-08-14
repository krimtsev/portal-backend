<?php

namespace App\Integrations\Mango\Resources\CallsStats;

use App\Integrations\Mango\Core\ApiResource;
use App\Integrations\Mango\Resources\CallsStats\DTO\CallsStatsRequestFilters;

final class CallsStatsResource extends ApiResource
{
    public function statsCallsRequest(CallsStatsRequestFilters $filters): array
    {
        return $this->client->send('stats/calls/request', $filters->jsonSerialize());
    }

    public function statsCallsResult(string $key): array
    {
        return $this->client->send('stats/calls/result', ['key' => $key]);
    }
}
