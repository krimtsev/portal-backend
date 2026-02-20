<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'      => 'required|string|min:3|max:255|unique:users,login',
            'name'       => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:users,email',
            'role'       => 'required|string',
            'password'   => 'required|string|min:8',
            'partner_id' => 'nullable|exists:partners,id',
            'disabled'   => 'boolean',
        ];
    }
}
