<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->notes ? trim($this->notes) : "",
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:users,login',
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'unique:users,email',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'role' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'partner_id' => [
                'nullable',
                'exists:partners,id',
            ],
            'disabled' => [
                'boolean',
            ],
            'departments' => [
                'nullable',
                'array',
            ],
            'departments.*' => [
                'required',
                'integer',
                'exists:departments,id',
            ],
            'access' => [
                'nullable',
                'array'
            ],
            'access.location_map' => [
                'nullable',
                'boolean'
            ],
        ];
    }
}
