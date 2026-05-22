<?php

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use App\Enums\Ticket\TicketState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id',
            ],
            'partner_id' => [
                'required',
                'integer',
                'exists:partners,id',
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
                'array',
            ],
            'files.*' => FileSettings::getRules(200),
        ];
    }
}
