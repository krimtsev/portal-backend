<?php

namespace App\Integrations\Yclients;

use App\Enums\HttpMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class YclientsClient
{
    private string $baseUrl = 'https://api.yclients.com/api/v1/';

    private string $logChannel = 'yclients';

    /** Фиксированный токен приложения */
    private string $appToken;

    /** Фиксированный токен разработчика */
    private string $partnerToken;

    /** Записывать в лог все запросы и ответы */
    private bool $isHttpDebug;

    public function __construct()
    {
        $this->appToken = config('yclients.app_token');
        $this->partnerToken = config('yclients.partner_token');
        $this->isHttpDebug = config('yclients.http.debug');
    }

    public function request(): PendingRequest
    {
        $http = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Accept'        => 'application/vnd.yclients.v2+json',
                'Content-Type'  => 'application/json',
                'Authorization' => sprintf('Bearer %s, User %s', $this->partnerToken, $this->appToken),
                'Connection'    => 'close',
            ])->withOptions([
                'verify'          => config('yclients.http.verify'),
                'timeout'         => config('yclients.http.timeout'),
                'connect_timeout' => config('yclients.http.connect_timeout'),
                'curl'            => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ]);

        if (config('yclients.http.use_retry')) {
            $http->retry(1, 5000);
        }

        if ($this->isHttpDebug) {
            $requestId = Str::uuid()->toString();

            $http->beforeSending(function (Request $request) use ($requestId) {
                Log::channel($this->logChannel)
                    ->info('Yclients HTTP Request', [
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
     * Единый метод для отправки всех запросов с обработкой ошибок
     *
     * @throws YclientsException
     */
    private function sendRequest(string $method, string $uri, array $data = []): array
    {
        $uri = ltrim($uri, '/');

        try {
            /** @var Response $response */
            $response = ($method === HttpMethod::GET->value)
                ? $this->request()->get($uri, $data)
                : $this->request()->$method($uri, $data);

        } catch (Throwable $e) {
            throw new YclientsException('Connection Error: ' . $e->getMessage(), 0, $e);
        }

        return $this->handleResponse($response);
    }

    protected function cleanParams(array $params): array
    {
        return array_filter($params, fn ($value) => $value !== null);
    }

    /**
     * @throws YclientsException
     */
    public function get(string $uri, array $query = []): array
    {
        $query = $this->cleanParams($query);

        return $this->sendRequest(HttpMethod::GET->value, $uri, $query);
    }

    /**
     * @throws YclientsException
     */
    public function post(string $uri, array $payload = []): array
    {
        return $this->sendRequest(HttpMethod::POST->value, $uri, $payload);
    }

    /**
     * @throws YclientsException
     */
    public function put(string $uri, array $payload = []): array
    {
        return $this->sendRequest(HttpMethod::PUT->value, $uri, $payload);
    }

    /**
     * @throws YclientsException
     */
    public function delete(string $uri, array $payload = []): array
    {
        return $this->sendRequest(HttpMethod::DELETE->value, $uri, $payload);
    }

    /**
     * @throws YclientsException
     */
    protected function handleResponse(Response $response): array
    {
        $responseBody = $response->body();
        $responseStatus = $response->status();
        $requestUrl = (string) $response->effectiveUri();

        if ($this->isHttpDebug) {
            $requestId = $response->header('X-Request-ID');

            Log::channel($this->logChannel)
                ->info('Yclients HTTP Response', [
                    'request-id' => $requestId,
                    'status'     => $responseStatus,
                    'body'       => $responseBody,
                ]);
        }

        if ($response->failed()) {
            throw new YclientsException(
                sprintf(
                    'HTTP request to [%s] failed with status [%d]. Response: %s',
                    $requestUrl,
                    $response->status(),
                    $responseBody
                ),
                $response->status()
            );
        }

        $responseData = $response->json();

        if (is_null($responseData)) {
            throw new YclientsException(
                sprintf(
                    'Failed to decode response as JSON from [%s]. Raw body: %s',
                    $requestUrl,
                    $responseBody
                )
            );
        }

        if (isset($responseData['errors'])) {
            throw new YclientsException(
                sprintf(
                    'URL [%s] returned internal errors: %s',
                    $requestUrl,
                    json_encode($responseData['errors'], JSON_UNESCAPED_UNICODE)
                )
            );
        }

        if (array_key_exists('success', $responseData) && !$responseData['success']) {
            throw new YclientsException(
                sprintf(
                    'Business transaction failed (success = false) for [%s]. Response: %s',
                    $requestUrl,
                    json_encode($responseData, JSON_UNESCAPED_UNICODE)
                )
            );
        }

        return $responseData;
    }
}
