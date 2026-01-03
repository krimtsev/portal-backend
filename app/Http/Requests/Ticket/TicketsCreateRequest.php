<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketsCreateRequest extends FormRequest
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
            'attributes' => [
                'nullable',
                'array'
            ],
            'message' => [
                'nullable',
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
