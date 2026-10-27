<?php

namespace App\Integrations\DaData\Resources\Bank;

use App\Integrations\DaData\Core\DaDataClient;
use App\Integrations\DaData\Settings;

final readonly class BankResource
{
    public function __construct(
        private DaDataClient $client
    ) {}

    public function suggest(
        string $query,
        int $count = Settings::SUGGESTION_COUNT,
        array $payload = [],
    ): array {
        $result = $this->client->suggestions->post('/suggest/bank', array_merge([
            'query' => $query,
            'count' => $count,
        ], $payload));

        return $result['suggestions'] ?? [];
    }

    public function findById(string $query, array $payload = []): array
    {
        return $this->client->suggestions->post('/findById/bank', array_merge([
            'query' => $query,
        ], $payload));
    }
}
