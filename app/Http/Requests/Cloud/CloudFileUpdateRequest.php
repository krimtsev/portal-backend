<?php

namespace App\Http\Requests\Cloud;

use Illuminate\Foundation\Http\FormRequest;

class CloudFileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255'
            ],
        ];
    }
}
