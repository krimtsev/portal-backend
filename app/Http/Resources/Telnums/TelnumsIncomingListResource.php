<?php

declare(strict_types=1);

namespace App\Http\Resources\Telnums;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TelnumsIncomingListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'partner_name'     => $this->name,
            'mango_telnum'     => $this->mango_telnum,
            'success_count'    => (int) $this->success_count,
            'failed_count'     => (int) $this->failed_count,
            'failed_percent'   => (int) $this->failed_percent,
            'total_count'      => (int) $this->total_count,
        ];
    }
}
