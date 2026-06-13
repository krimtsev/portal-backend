<?php

namespace App\Integrations\Yclients\Resources\Comments\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

final class CommentsFilters extends BaseRequest
{
    public function __construct(
        public readonly ?int $page = 1,
        public readonly ?int $count = 500,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?int $staffId = null,
        public readonly ?int $rating = null,
    ) {}
}
