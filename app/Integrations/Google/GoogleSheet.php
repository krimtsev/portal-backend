<?php

namespace App\Integrations\Google;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheet
{
    private $client;

    private $service;

    public function __construct()
    {
        $this->client = $this->getClient();
        $this->service = new Sheets($this->client);
    }

    public function getClient()
    {
        $client = new Client();
        $client->setScopes(Sheets::SPREADSHEETS);
        $client->setAuthConfig(config('partner.secrets.google.credentials'));
        $client->setAccessType('offline');

        return $client;
    }

    public function readSheet($sheet)
    {
        $range = sprintf('%s!%s', $sheet->spreadsheet_name, $sheet->spreadsheet_range);
        $result = $this->service->spreadsheets_values->get($sheet->spreadsheet_id, $range);

        return $result->getValues();
    }
}
