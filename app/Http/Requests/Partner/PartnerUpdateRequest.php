<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class PartnerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization' => [
                'nullable',
                'string',
                'max:255'
            ],
            'inn' => [
                'nullable',
                'string',
                'size:12'
            ],
            'ogrnip' => [
                'nullable',
                'string',
                'max:15'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'contract_number' => [
                'nullable',
                'string',
                'max:50'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'yclients_id' => [
                'nullable',
                'string',
                'max:255'
            ],
            'mango_telnum' => [
                'nullable',
                'string',
                'max:255'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255'
            ],
            'start_at' => [
                'nullable',
                'date'
            ],
            'disabled' => [
                'boolean'
            ],
            'group_id' => [
                'nullable',
                'integer',
                'exists:partner_groups,id',
            ],
            'telnums' => [
                'nullable',
                'array'
            ],
            'telnums.*.id' => [
                'nullable',
                'integer',
                'exists:partner_telnums,id'
            ],
            'telnums.*.name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'telnums.*.number' => [
                'required',
                'string',
                'max:20'
            ],
        ];
    }
}
