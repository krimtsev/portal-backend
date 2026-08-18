<?php

namespace App\Integrations\Telegram\Transport;

use App\Integrations\Telegram\DTO\TelegramResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TelegramTransport
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
            $http->retry(2, 5000);
        }

        $this->applyProxy($http);

        return $http;
    }

    public function send(string $token, string $method, array $payload): TelegramResponse
    {
        $client = $this->request();

        $uri = sprintf('bot%s/%s', $token, $method);

        array_walk_recursive($payload, function (&$item) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        [$data, $multipart] = $this->extractMultipart($payload);

        try {
            if (!empty($multipart)) {
                foreach ($multipart as $name => $file) {
                    $client->attach($name, $file['contents'], $file['filename']);
                }
                $response = $client->post($uri, $data);
            } else {
                $response = $client->asForm()->post($uri, $data);
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
        $proxy = $this->proxyConfig['proxy'] ?? [];
        $enabled = (bool) ($proxy['enabled'] ?? false);
        $list = array_filter($proxy['list'] ?? []);

        if (!$enabled || empty($list)) {
            return;
        }

        $proxyIp = $list[array_rand($list)];
        $username = $proxy['username'] ?? null;
        $password = $proxy['password'] ?? null;
        $scheme = strtolower($proxy['scheme'] ?? 'http');
        $proxyType = $proxy['type'] ?? CURLPROXY_SOCKS5_HOSTNAME;

        $proxyUrl = "{$scheme}://{$proxyIp}";

        $curlOptions = [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_PROXYTYPE => $proxyType
        ];

        if ($username !== null && $password !== null) {
            $curlOptions[CURLOPT_PROXYUSERPWD] = "{$username}:{$password}";
        }

        $client->withOptions([
            'proxy' => $proxyUrl,
            'curl'  => $curlOptions,
        ]);
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
                    ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)
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
