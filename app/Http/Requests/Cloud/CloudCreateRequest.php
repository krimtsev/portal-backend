<?php

namespace App\Http\Requests\Cloud;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloudCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cloud_folders', 'slug')
            ],
            'category_id' => [
                'integer',
                Rule::exists('cloud_folders', 'id')
            ],
        ];
    }
}
