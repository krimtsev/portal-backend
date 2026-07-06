<?php

declare(strict_types=1);

namespace App\Http\Requests\Cloud;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

final class CloudFileUploadRequest extends FormRequest
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
