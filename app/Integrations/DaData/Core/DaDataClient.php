<?php

namespace App\Integrations\DaData\Core;

use App\Integrations\DaData\DaDataBaseClient;

readonly class DaDataClient
{
    public DaDataBaseClient $suggestions;
    public DaDataBaseClient $clean;

    public function __construct()
    {
        $this->suggestions = new DaDataBaseClient(
            baseUrl:    (string) config('dadata.api.suggestions_url'),
            withSecret: false,
        );

        $this->clean = new DaDataBaseClient(
            baseUrl:    (string) config('dadata.api.cleaner_url'),
            withSecret: true,
        );
    }
}
