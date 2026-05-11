<?php

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use App\Enums\Department;
use App\Enums\Ticket\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TicketCreateRequest extends FormRequest
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
            ],
            'department' => [
                'required',
                new Enum(Department::class),
            ],
            'partner_id' => [
                'required',
                'integer',
                'exists:partners,id'
            ],
            'attributes' => [
                'nullable',
                'array'
            ],
            'type' => [
                'required',
                new Enum(TicketType::class),
            ],
            'message' => [
                'nullable',
                'string',
            ],
            'files' => [
                'nullable',
                'array'
            ],
            'files.*' => FileSettings::getRules(200),
        ];
    }
}
