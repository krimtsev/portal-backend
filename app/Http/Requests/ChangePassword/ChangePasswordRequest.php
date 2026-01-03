<?php

namespace App\Http\Requests\ChangePassword;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:' . config('validation.password'),
                'same:confirmPassword'
            ],
            'confirmPassword' => [
                'required',
                'string'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain letters and numbers and have no spaces.',
            'password.same' => 'Password and confirmation do not match.'
        ];
    }
}
