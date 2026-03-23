<?php

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

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
            'type' => [
                'string'
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
