<?php

namespace App\Http\Requests\Cloud;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloudUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $folder = $this->route('folder');
        $folderId = $folder ? $folder->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cloud_folders', 'slug')->ignore($folderId)
            ],

            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('cloud_folders', 'id'),
                function ($attribute, $value, $fail) use ($folder) {
                    if (!$folder) return;

                    if (is_null($folder->category_id) && !is_null($value)) {
                        $fail(trans('cloud.validation.root_cannot_have_parent'));
                    }

                    if (!is_null($folder->category_id) && is_null($value)) {
                        $fail(trans('cloud.validation.cannot_move_to_root'));
                    }
                },
            ],
        ];
    }
}
