<?php

declare(strict_types=1);

namespace App\Http\Resources\BankDetail;

use App\Models\BankDetail\BankDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BankDetail
 */
final class BankDetailUserOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id'   => $this->partner->id,
                    'name' => $this->partner->name,
                ];
            }),
            'entrepreneur_name'     => $this->entrepreneur_name,
            'inn'                   => $this->inn,
            'checking_account'      => $this->checking_account,
            'bank_name'             => $this->bank_name,
            'bik'                   => $this->bik,
            'correspondent_account' => $this->correspondent_account,
        ];
    }
}
