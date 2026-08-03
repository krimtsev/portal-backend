<?php

namespace App\Integrations\Telegram\DTO;

readonly class TelegramResponse
{
    public function __construct(
        public bool $ok,
        public array $result = [],
        public ?string $errorDescription = null,
        public ?int $errorCode = null,
    ) {}

    public static function fromResponse(array $data, int $status): self
    {
        return new self(
            ok: $data['ok'] ?? ($status >= 200 && $status < 300),
            result: $data['result'] ?? [],
            errorDescription: $data['description'] ?? null,
            errorCode: $data['error_code'] ?? null,
        );
    }

    public static function fromError(string $description, int $code = 500): self
    {
        return new self(
            ok: false,
            result: [],
            errorDescription: $description,
            errorCode: $code,
        );
    }
}
