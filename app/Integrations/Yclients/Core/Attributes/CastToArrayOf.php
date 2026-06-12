<?php

namespace App\Integrations\Yclients\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class CastToArrayOf
{
    public function __construct(
        public string $className
    ) {}
}
