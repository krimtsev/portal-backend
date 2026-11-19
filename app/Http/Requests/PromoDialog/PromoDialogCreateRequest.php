<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoDialog;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

final class PromoDialogCreateRequest extends FormRequest
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
                'max:125',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:' . FileSettings::IMAGE_WEB_MIMES,
                'max:5120',
            ],
            'remove_image' => [
                'sometimes',
                'boolean',
            ],
            'disabled' => [
                'boolean',
            ],
        ];
    }
}
