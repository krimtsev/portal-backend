<?php

namespace App\Http\Requests\DaData;

use App\Enums\DaData\DaDataStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DaDataBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => [
                'required',
                'string',
                'max:500'
            ],
            'count' => [
                'sometimes',
                'integer',
                'min:1',
                'max:20'
            ],
            'status' => [
                'sometimes',
                'array',
            ],
            'status.*' => [
                'string',
                Rule::in(array_column(DaDataStatus::cases(), 'value')),
            ],
        ];
    }
}
