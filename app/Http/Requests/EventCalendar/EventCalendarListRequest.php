<?php

namespace App\Http\Requests\EventCalendar;

use App\Http\Requests\BaseListRequest;

final class EventCalendarListRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), []);
    }
}
