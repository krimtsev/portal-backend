<?php

declare(strict_types=1);

namespace App\Integrations\Mango;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MangoException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Логирование исключения.
     */
    public function report(): bool
    {
        Log::channel('mango')
            ->error($this->getMessage(), [
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
            ]);

        return false;
    }
}
