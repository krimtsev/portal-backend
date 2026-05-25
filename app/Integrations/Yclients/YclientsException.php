<?php

namespace App\Integrations\Yclients;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

class YclientsException extends Exception {

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Логирование исключения.
     */
    public function report(): bool
    {
        Log::channel('yclients')
            ->error($this->getMessage(), [
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
            ]);

        return false;
    }
}

