<?php

declare(strict_types=1);

namespace App\Exceptions\Partner;

use App\Enums\Partner\PartnerCode;
use RuntimeException;

final class UnknownPartnerException extends RuntimeException
{
    public static function forCode(?string $code): self
    {
        return new self(sprintf(
            'Unknown partner "%s" in config "partner.current" (env PARTNER_NAME). Supported: %s.',
            $code ?? 'null',
            implode(', ', array_column(PartnerCode::cases(), 'value')),
        ));
    }
}
