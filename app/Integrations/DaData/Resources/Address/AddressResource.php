<?php

namespace App\Integrations\DaData\Resources\Address;

use App\Integrations\DaData\Core\DaDataClient;
use App\Integrations\DaData\Settings;

final readonly class AddressResource
{
    public function __construct(
        private DaDataClient $client
    ) {}

    public function suggest(
        string $query,
        int $count = Settings::SUGGESTION_COUNT,
        array $payload = [],
    ): array {
        $result = $this->client->suggestions->post('/suggest/address', array_merge([
            'query' => $query,
            'count' => $count,
        ], $payload));

        return $result['suggestions'] ?? [];
    }

    public function findById(array $payload = []): array
    {
        return $this->client->clean->post('/clean/address', $payload);
    }
}
