<?php

namespace App\Integrations\Mango\Resources\Bwlists;

use App\Integrations\Mango\Core\ApiResource;

final class BwlistsResource extends ApiResource
{
    public function getBwlists(): array
    {
        return $this->client->send('bwlists/numbers');
    }
}
