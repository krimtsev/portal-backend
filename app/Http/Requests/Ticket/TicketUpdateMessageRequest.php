<?php

namespace App\Http\Requests\Ticket;

use App\Constants\File\FileSettings;
use Illuminate\Foundation\Http\FormRequest;

class TicketUpdateMessageRequest extends FormRequest
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
            'files'   => ['nullable', 'array'],
            'files.*' => FileSettings::getRules(200),
        ];
    }
}
