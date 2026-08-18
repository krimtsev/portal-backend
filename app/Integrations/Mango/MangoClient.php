<?php

namespace App\Integrations\Mango;

use App\Integrations\Mango\Core\MangoConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class MangoClient
{
    private string $baseUrl = 'https://app.mango-office.ru/vpbx/';

    private readonly MangoConfig $config;

    public function __construct()
    {
        $this->config = new MangoConfig(
            apiKey: config('mango.api.key'),
            apiSalt: config('mango.api.salt'),
        );
    }

    public function request(): PendingRequest
    {
        $http = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Connection' => 'keep-alive',
            ])
            ->withOptions([
                'verify'          => config('mango.http.verify'),
                'timeout'         => config('mango.http.timeout'),
                'connect_timeout' => config('mango.http.connect_timeout'),
                'curl'            => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->asForm();

        if (config('mango.http.use_retry')) {
            $http->retry(2, 5000);
        }

        return $http;
    }

    public function generateSign(string $jsonPayload): string
    {
        return hash('sha256', $this->config->apiKey . $jsonPayload . $this->config->apiSalt);
    }

    public function send(string $uri, array $data = []): array
    {
        try {
            $client = $this->request();

            $jsonPayload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $signature = $this->generateSign($jsonPayload);

            $response = $client->post($uri, [
                'vpbx_api_key' => $this->config->apiKey,
                'sign'         => $signature,
                'json'         => $jsonPayload,
            ]);

            if ($response->status() !== 200) {
                throw new MangoException(
                    "Mango API error HTTP {$response->status()}: {$response->body()}"
                );
            }

            return $response->json() ?? [];
        } catch (Throwable $e) {
            throw new MangoException('Connection Error: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
