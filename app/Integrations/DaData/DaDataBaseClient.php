<?php

namespace App\Integrations\DaData;

use App\Integrations\DaData\Core\DaDataConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class DaDataBaseClient
{
    private readonly DaDataConfig $config;

    private readonly string $baseUrl;

    /** Записывать в лог все запросы и ответы */
    private readonly bool $isHttpDebug;

    /** Запрос с использование API SECRET KEY */
    private readonly bool $withSecret;

    private string $logChannel = 'dadata';

    public function __construct(
        string $baseUrl,
        bool $withSecret
    ) {
        $this->config = new DaDataConfig(
            apiKey: config('dadata.api.key'),
            apiSecretKey: config('dadata.api.secret_key'),
        );

        $this->isHttpDebug = config('dadata.http.debug');

        $this->baseUrl = $baseUrl;
        $this->withSecret = $withSecret;
    }

    public function createRequest(): PendingRequest
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => sprintf('Token %s', $this->config->apiKey),
            'Connection'    => 'close',
        ];

        if ($this->withSecret) {
            $headers['X-Secret'] = $this->config->apiSecretKey;
        }

        $http = Http::baseUrl($this->baseUrl)
            ->withHeaders($headers)
            ->withOptions([
                'verify'          => config('dadata.http.verify'),
                'timeout'         => config('dadata.http.timeout'),
                'connect_timeout' => config('dadata.http.connect_timeout'),
                'curl'            => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->asJson();

        if (config('dadata.http.use_retry')) {
            $http->retry(2, 5000);
        }

        if ($this->isHttpDebug) {
            $requestId = Str::uuid()->toString();

            $http->beforeSending(function (Request $request) use ($requestId) {
                Log::channel($this->logChannel)
                    ->info('DaData HTTP Request', [
                        'request-id' => $requestId,
                        'method'     => $request->method(),
                        'url'        => $request->url(),
                        'payload'    => $request->data(),
                    ]);
            });

            $http->withResponseMiddleware(function (ResponseInterface $response) use ($requestId) {
                return $response->withHeader('X-Request-ID', $requestId);
            });
        }

        return $http;
    }

    /**
     * GET запрос к API
     */
    public function get($url, $query = [])
    {
        $response = $this->createRequest()->get($url, $query);

        return $this->handleResponse($response);
    }

    /**
     * POST запрос к API
     */
    public function post($url, $data)
    {
        $response = $this->createRequest()->post($url, $data);

        return $this->handleResponse($response);
    }

    protected function handleResponse(Response $response): array
    {
        $responseBody = $response->body();
        $responseStatus = $response->status();
        $requestUrl = (string) $response->effectiveUri();

        if ($this->isHttpDebug) {
            $requestId = $response->header('X-Request-ID');
            $payload = $response->json();

            Log::channel($this->logChannel)
                ->info('DaData HTTP Response', [
                    'request-id' => $requestId,
                    'status'     => $responseStatus,
                    'body'       => $payload ?? $responseBody,
                ]);
        }

        if ($response->status() !== 200) {
            throw new DaDataException(
                sprintf(
                    'HTTP request to [%s] failed with status [%d]. Response: %s',
                    $requestUrl,
                    $response->status(),
                    $responseBody
                ),
                $response->status()
            );
        }

        return $response->json() ?? [];
    }
}
