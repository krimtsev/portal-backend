<?php

namespace App\Http\Requests\Telnums;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TelnumsIncomingPartnerDayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => [
                'required',
                'date_format:Y-m-d',
            ],
            'date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ];
    }
}
