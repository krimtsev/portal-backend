<?php

namespace App\Http\Requests\EventCalendar;

use Illuminate\Foundation\Http\FormRequest;

final class EventCalendarCreateRequest extends FormRequest
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
            'description' => [
                'nullable',
                'string',
            ],
            'start_at' => [
                'required',
                'date',
            ],
            'end_at' => [
                'required',
                'date',
                'after_or_equal:start_at',
            ],
            'department_id' => [
                'nullable',
                'exists:departments,id',
            ],
            'responsible_user_ids' => [
                'nullable',
                'array',
            ],
            'responsible_user_ids.*' => [
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
