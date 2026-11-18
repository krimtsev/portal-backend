<?php

namespace App\Http\Requests\Promotion;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

class PromotionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => [
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string'
            ],
            'image'       => [
                'nullable',
                'image',
                'mimes:' . FileSettings::IMAGE_WEB_MIMES,
                'max:5120'
            ],
            'disabled'    => [
                'sometimes',
                'boolean'
            ],
            'expires_at'  => [
                'nullable',
                'date'
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('disabled')) {
            $this->merge(['disabled' => $this->boolean('disabled')]);
        }
    }
}
