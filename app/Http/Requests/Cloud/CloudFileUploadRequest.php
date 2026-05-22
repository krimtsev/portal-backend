<?php

namespace App\Http\Requests\Cloud;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

class CloudFileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => [
                'required',
                'array',
            ],
            'files.*' => FileSettings::getRules(200),
        ];
    }
}
