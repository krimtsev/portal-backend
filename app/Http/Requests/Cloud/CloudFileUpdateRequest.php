<?php

declare(strict_types=1);

namespace App\Http\Requests\Cloud;

use Illuminate\Foundation\Http\FormRequest;

final class CloudFileUpdateRequest extends FormRequest
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
                'max:255',
            ],
        ];
    }
}
