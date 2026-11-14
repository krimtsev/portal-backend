<?php

namespace App\Http\Requests\BankDetail;

use App\Http\Requests\BaseListRequest;
use App\Models\User\User;

final class BankDetailListRequest extends BaseListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->attributes->get('is_dashboard', false)) {
            return;
        }

        $user = $this->user();
        if (!$user instanceof User) {
            return;
        }

        $filters = (array) $this->input('filters', []);

        $filters['user_id'] = [$user->id];

        if (!empty($filters['partner_id'])) {
            $validPartners = collect($filters['partner_id'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->intersect($user->allowedPartnerIds())
                ->values()
                ->all();

            if (!empty($validPartners)) {
                $filters['partner_id'] = $validPartners;
            } else {
                unset($filters['partner_id']);
            }
        }

        $this->merge(['filters' => $filters]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'filters.user_id' => [
                'nullable',
                'array',
            ],
            'filters.user_id.*' => [
                'integer',
            ],
            'filters.partner_id' => [
                'nullable',
                'array',
            ],
            'filters.partner_id.*' => [
                'integer',
            ],
        ]);
    }
}
