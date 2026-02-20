<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'login'      => 'sometimes|string|max:255|unique:users,login,' . $userId,
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|nullable|email|unique:users,email,' . $userId,
            'role'       => 'sometimes|string',
            'password'   => 'sometimes|nullable|string|min:8',
            'partner_id' => 'nullable|exists:partners,id',
            'disabled'   => 'sometimes|boolean',
        ];
    }
}
