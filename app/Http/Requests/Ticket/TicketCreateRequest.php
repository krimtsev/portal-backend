<?php

declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

final class TicketCreateRequest extends FormRequest
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
            'attributes' => [
                'nullable',
                'array',
            ],
            'type' => [
                'required',
                'string',
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
