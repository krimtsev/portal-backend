<?php

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use App\Enums\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Ticket\TicketState;

class TicketUpdateRequest extends FormRequest
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
            'state' => [
                'required',
                new Enum(TicketState::class),
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
