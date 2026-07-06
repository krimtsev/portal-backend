<?php

declare(strict_types=1);

namespace App\Http\Requests\ChangePassword;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
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
                'same:confirmPassword',
            ],
            'confirmPassword' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain letters and numbers and have no spaces.',
            'password.same'  => 'Password and confirmation do not match.',
        ];
    }
}
