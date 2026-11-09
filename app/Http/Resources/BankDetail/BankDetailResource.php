<?php

declare(strict_types=1);

namespace App\Http\Resources\BankDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BankDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'partner_id'            => $this->partner_id,
            'entrepreneur_name'     => $this->entrepreneur_name,
            'inn'                   => $this->inn,
            'checking_account'      => $this->checking_account,
            'bank_name'             => $this->bank_name,
            'bik'                   => $this->bik,
            'correspondent_account' => $this->correspondent_account,
        ];
    }
}
