<?php

declare(strict_types=1);

namespace App\Http\Requests\PromoDialog;

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
            'disabled' => [
                'boolean',
            ],
        ];
    }
}
