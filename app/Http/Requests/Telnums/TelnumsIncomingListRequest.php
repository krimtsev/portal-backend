<?php

declare(strict_types=1);

namespace App\Http\Requests\Telnums;

use App\Http\Requests\BaseListRequest;

final class TelnumsIncomingListRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'filters.date_from' => [
                'required',
                'date_format:Y-m-d'
            ],
            'filters.date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:filters.date_from',
            ],
            'filters.partner_id' => [
                'nullable',
                'array'
            ],
        ]);
    }
}
