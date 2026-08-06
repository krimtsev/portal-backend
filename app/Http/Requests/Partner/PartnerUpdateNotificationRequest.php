<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

final class PartnerUpdateNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_channel'                  => ['sometimes', 'array'],
            'notification_channel.send_telegram'    => ['sometimes', 'boolean'],
            'notification_channel.telegram_chat_id' => ['nullable', 'string'],
            'notification_channel.check_payment'    => ['sometimes', 'boolean'],
            'notification_channel.payment_date'     => ['nullable', 'date:Y-m-d'],

            'report_settings'                       => ['sometimes', 'array'],
            'report_settings.lost_clients_days'     => ['sometimes', 'integer', 'min:0', 'max:365'],
            'report_settings.returned_clients_days' => ['sometimes', 'integer', 'min:0', 'max:365'],
            'report_settings.new_clients_days'      => ['sometimes', 'integer', 'min:0', 'max:365'],
        ];
    }
}
