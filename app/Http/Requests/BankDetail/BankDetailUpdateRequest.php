<?php

namespace App\Http\Requests\BankDetail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BankDetailUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'partner_id' => [
                'nullable',
                'integer',
                Rule::exists('partners', 'id'),
            ],
            'entrepreneur_name' => [
                'required',
                'string',
                'max:255',
            ],
            'inn' => [
                'nullable',
                'string',
                'regex:/^\d{12}$/',
            ],
            'checking_account' => [
                'nullable',
                'string',
                'regex:/^\d{20}$/',
            ],
            'bank_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bik' => [
                'nullable',
                'string',
                'regex:/^\d{9}$/',
            ],
            'correspondent_account' => [
                'nullable',
                'string',
                'regex:/^\d{20}$/',
            ],
        ];
    }
}
