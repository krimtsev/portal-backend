<?php

namespace App\Integrations\Mango;

use App\Integrations\Mango\Core\MangoConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class MangoClient
{
    private string $baseUrl = 'https://app.mango-office.ru/vpbx/';

    private readonly MangoConfig $config;

    private string $logChannel = 'mango';

    /** Записывать в лог все запросы и ответы */
    private bool $isHttpDebug;

    public function __construct()
    {
        $this->config = new MangoConfig(
            apiKey: config('mango.api.key'),
            apiSalt: config('mango.api.salt'),
        );

        $this->isHttpDebug = config('mango.http.debug');
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

        if ($this->isHttpDebug) {
            $requestId = Str::uuid()->toString();

            $http->beforeSending(function (Request $request) use ($requestId) {
                $payload = $request->data();

                if (isset($payload['vpbx_api_key'])) {
                    $payload['vpbx_api_key'] = '********';
                }

                Log::channel($this->logChannel)
                    ->info('Mango HTTP Request', [
                        'request-id' => $requestId,
                        'method'     => $request->method(),
                        'url'        => $request->url(),
                        'payload'    => $payload,
                    ]);
            });

            $http->withResponseMiddleware(function (ResponseInterface $response) use ($requestId) {
                return $response->withHeader('X-Request-ID', $requestId);
            });
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

        } catch (Throwable $e) {
            throw new MangoException('Connection Error: ' . $e->getMessage(), $e->getCode(), $e);
        }

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

            if (is_array($payload)) {
                if (isset($payload['data'])) {
                    $payload['data'] = '[REMOVED]';
                }
            }

            Log::channel($this->logChannel)
                ->info('Mango HTTP Response', [
                    'request-id' => $requestId,
                    'status'     => $responseStatus,
                    'body'       => $payload ?? $responseBody,
                ]);
        }

        if ($response->status() !== 200) {
            throw new MangoException(
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
