<?php

namespace App\Integrations\Yclients\Resources\Comments\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class CommentsResponse extends BaseResponse
{
    public function __construct(
        public int $id,
        public int $salon_id,
        public int $type,
        public int $master_id,
        public int $rating,
        public string $text,
        public string $date,

    ) {}

    protected static function getInputMapping(): array
    {
        return [
            'id'        => 'data.id',
            'salon_id'  => 'data.salon_id',
            'type'      => 'data.type',
            'master_id' => 'data.master_id',
            'text'      => 'data.text',
            'date'      => 'data.date',
            'rating'    => 'data.rating',
        ];
    }
}
