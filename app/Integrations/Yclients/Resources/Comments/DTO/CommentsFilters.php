<?php

namespace App\Integrations\Yclients\Resources\Comments\DTO;

use App\Integrations\Yclients\Core\BaseRequest;

class CommentsFilters extends BaseRequest
{
    public function __construct(
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?int $staffId = null,
        public ?int $rating = null,
        public ?int $page = null,
        public ?int $count = null,
    ) {}
}
