<?php

declare(strict_types=1);

namespace App\Http\Requests\Statistics;

use App\Http\Requests\BaseListRequest;

final class StatisticsStaffDetailsRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => [
                'required',
                'integer',
            ],
            'staff_id' => [
                'required',
                'integer',
            ],
            'date' => [
                'required',
                'date',
            ],
        ];
    }
}
