<?php

declare(strict_types=1);

namespace App\Http\Requests\Statistics;

use App\Http\Requests\BaseListRequest;

final class StatisticsStaffRequest extends BaseListRequest
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
        ];
    }
}
