<?php

namespace App\Http\Requests\Statistics;

use App\Http\Requests\BaseListRequest;

final class StatisticsPartnerRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filters.date' => [
                'required',
                'date',
            ],
            'filters.partner_id' => [
                'required',
                'integer',
            ],
            'filters.months_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
        ];
    }
}
