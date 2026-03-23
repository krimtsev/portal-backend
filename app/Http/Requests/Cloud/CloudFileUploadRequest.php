<?php

namespace App\Http\Requests\Cloud;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\File\FileSettings;

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
                'array'
            ],
            'files.*' => FileSettings::getRules(200),
        ];
    }
}
