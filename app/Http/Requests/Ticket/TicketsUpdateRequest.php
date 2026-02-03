<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Ticket\TicketState;

class TicketsUpdateRequest extends FormRequest
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
            'category_id' => [
                'required',
                'integer',
                'exists:tickets_categories,id',
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
            'files.*' => [
                'file',
                'max:1024',
                'mimes:jpg,jpeg,png,webp,tif,pdf,doc,docx,zip,xlsx,xls,txt,ai,pptx',
            ],
        ];
    }
}
