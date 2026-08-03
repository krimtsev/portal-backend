<?php

namespace App\Integrations\Telegram\Transport;

use App\Integrations\Telegram\DTO\TelegramResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramTransport
{
    private string $baseUrl = 'https://api.telegram.org';

    private string $logChannel = 'telegram';

    public function __construct(
        private readonly array $proxyConfig
    ) {}

    public function request(): PendingRequest
    {
        $http = Http::baseUrl($this->baseUrl)
            ->withOptions([
                'verify'          => config('telegram.http.verify'),
                'timeout'         => config('telegram.http.timeout'),
                'connect_timeout' => config('telegram.http.connect_timeout'),
                'curl'            => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ]);

        if (config('telegram.http.use_retry')) {
            $http->retry(1, 5000);
        }

        $this->applyProxy($http);

        return $http;
    }

    public function send(string $token, string $method, array $payload): TelegramResponse
    {
        $client = $this->request();

        $uri = sprintf('bot%s/%s', $token, $method);

        [$data, $multipart] = $this->extractMultipart($payload);

        try {
            if (!empty($multipart)) {
                foreach ($multipart as $name => $file) {
                    $client->attach($name, $file['contents'], $file['filename']);
                }
                $response = $client->post($uri, $data);
            } else {
                $response = $client->asJson()->post($uri, $data);
            }

            $this->logRequest($token, $method, $payload, $response->json());

            return TelegramResponse::fromResponse($response->json() ?? [], $response->status());

        } catch (Throwable $e) {
            Log::channel($this->logChannel)->error("Telegram Transport Error: [{$method}]", [
                'error' => $e->getMessage(),
            ]);

            return TelegramResponse::fromError($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    private function applyProxy(PendingRequest $client): void
    {
        if (empty($this->proxyConfig['enabled']) || empty($this->proxyConfig['list'])) {
            return;
        }

        $proxyIp = $this->proxyConfig['list'][array_rand($this->proxyConfig['list'])];
        $username = $this->proxyConfig['username'] ?? null;
        $password = $this->proxyConfig['password'] ?? null;
        $scheme = $this->proxyConfig['scheme'] ?? 'http';

        $auth = ($username && $password) ? "{$username}:{$password}@" : '';

        $proxyUrl = "{$scheme}://{$auth}{$proxyIp}";

        $client->withOptions(['proxy' => $proxyUrl]);
    }

    private function extractMultipart(array $payload): array
    {
        $data = [];
        $multipart = [];

        foreach ($payload as $key => $value) {
            if ($value instanceof UploadedFile) {
                $multipart[$key] = [
                    'contents' => $value->get(),
                    'filename' => $value->getClientOriginalName(),
                ];
            } elseif (is_resource($value)) {
                $multipart[$key] = [
                    'contents' => stream_get_contents($value),
                    'filename' => basename(stream_get_meta_data($value)['uri'] ?? "file_{$key}"),
                ];
            } elseif (is_string($value) && file_exists($value) && is_file($value)) {
                $multipart[$key] = [
                    'contents' => file_get_contents($value),
                    'filename' => basename($value),
                ];
            } else {
                $data[$key] = is_array($value)
                    ? json_encode($value)
                    : $value;
            }
        }

        return [$data, $multipart];
    }

    private function logRequest(string $token, string $method, array $payload, ?array $response): void
    {
        if (!config('telegram.http.debug')) {
            return;
        }

        $cleanPayload = collect($payload)->map(fn ($v) => is_resource($v) || $v instanceof UploadedFile ? '[FILE]' : $v)->toArray();
        $maskedToken = substr($token, 0, 8) . '***';

        Log::channel($this->logChannel)->debug("API Call: {$method}", [
            'bot'      => $maskedToken,
            'payload'  => $cleanPayload,
            'response' => $response,
        ]);
    }
}
