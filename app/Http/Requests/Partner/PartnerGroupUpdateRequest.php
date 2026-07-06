<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

final class PartnerGroupUpdateRequest extends FormRequest
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
            'partners' => [
                'nullable',
                'array',
            ],
            'partners.*' => [
                'exists:partners,id',
            ],
        ];
    }
}
