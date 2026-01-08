<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketsUpdateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => [
                'string',
            ],
            'files' => ['nullable', 'array'],
            'files.*' => [
                'file',
                'max:1024',
                'mimes:jpg,jpeg,png,tif,pdf,doc,docx,zip,xlsx,xls,txt,ai,pptx',
            ],
        ];
    }
}
