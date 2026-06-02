<?php

namespace App\Http\Requests\Royalty;

use App\Http\Requests\BaseListRequest;

class RoyaltyListRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'filters.date' => [
                'required',
                'date',
            ],
            'filters.partner_id' => [
                'nullable',
                'array',
            ],
        ]);
    }
}
