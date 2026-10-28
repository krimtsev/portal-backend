<?php

namespace App\Integrations\DaData\Resources\Party;

use App\Integrations\DaData\Core\DaDataClient;
use App\Integrations\DaData\Settings;

final readonly class PartyResource
{
    public function __construct(
        private DaDataClient $client
    ) {}

    public function suggest(
        string $query,
        int $count = Settings::SUGGESTION_COUNT,
        array $payload = [],
    ): array {
        $result = $this->client->suggestions->post('/suggest/party', array_merge([
            'query' => $query,
            'count' => $count,
        ], $payload));

        return $result['suggestions'] ?? [];
    }

    public function findById(
        string $query,
        int $count = Settings::SUGGESTION_COUNT,
        array $payload = []
    ): array
    {
        $result = $this->client->suggestions->post('/findById/party', array_merge([
            'query' => $query,
            'count' => $count,
        ], $payload));

        return $result['suggestions'] ?? [];
    }
}
