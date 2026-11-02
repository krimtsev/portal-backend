<?php

namespace App\Http\Requests\PromoDialog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PromoDialogReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('promo_dialogs', 'id'),
            ],
        ];
    }

    /**
     * Подмешиваем route-параметр в данные валидации,
     * чтобы правило exists сработало для {id} из URL.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'Не указан идентификатор промо-диалога.',
            'id.integer'  => 'Идентификатор промо-диалога должен быть целым числом.',
            'id.exists'   => 'Промо-диалог с таким идентификатором не найден.',
        ];
    }

    public function promoDialogId(): int
    {
        return (int) $this->validated('id');
    }
}
