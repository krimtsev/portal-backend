<?php

declare(strict_types=1);

namespace App\Http\Resources\BankDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BankDetailExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'entrepreneur_name'     => $this->entrepreneur_name,
            'inn'                   => $this->inn,
            'checking_account'      => $this->checking_account,
            'bank_name'             => $this->bank_name,
            'bik'                   => $this->bik,
            'correspondent_account' => $this->correspondent_account,
            'created_at'            => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'            => $this->updated_at?->format('Y-m-d H:i:s'),
            'user'                  => $this->whenLoaded('user', fn () => $this->user?->login),
            'partner'               => $this->whenLoaded('partner', fn () => $this->partner?->name),
        ];
    }
}
