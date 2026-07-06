<?php

declare(strict_types=1);

namespace App\Http\Requests\Statistics;

use App\Http\Requests\BaseListRequest;

final class StatisticsTotalCompareRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
            ],
            'partner_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
