<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class PartnerSendMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'partner_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'partner_ids.*' => [
                'required',
                'regex:/^(!all|!test|\d+)$/'
            ],
            'message' => [
                'required',
                'string',
                'max:4096'
            ],
            'file'  => [
                'nullable',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt'
            ],
        ];
    }
}
