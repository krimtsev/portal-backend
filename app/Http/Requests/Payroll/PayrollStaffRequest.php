<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Http\Requests\BaseListRequest;

final class PayrollStaffRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filters.start_date' => [
                'required',
                'date'
            ],
            'filters.end_date'   => [
                'required',
                'date',
                'after_or_equal:start_date'
            ],
            'filters.partner_id' => [
                'required',
                'numeric',
            ],
        ];
    }
}
