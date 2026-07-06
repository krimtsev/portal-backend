<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'perPage' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sortBy' => [
                'nullable',
                'string',
            ],
            'sortOrder' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'filters' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function filters(): array
    {
        return $this->input('filters', []);
    }
}
