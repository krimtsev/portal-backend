<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class DocumentDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $type_id,
        public readonly int $storage_id,
        public readonly int $user_id,
        public readonly int $company_id,
        public readonly int $number,
        public readonly ?string $comment,
        public readonly string $date_created,
        public readonly int $category_id,
        public readonly int $visit_id,
        public readonly int $record_id,
        public readonly string $type_title,
        public readonly ?bool $is_sale_bill_printed,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'                   => ['required', 'integer'],
            'type_id'              => ['required', 'integer'],
            'storage_id'           => ['required', 'integer'],
            'user_id'              => ['required', 'integer'],
            'company_id'           => ['required', 'integer'],
            'number'               => ['required', 'integer'],
            'comment'              => ['nullable', 'string'],
            'date_created'         => ['required', 'string'],
            'category_id'          => ['required', 'integer'],
            'visit_id'             => ['required', 'integer'],
            'record_id'            => ['required', 'integer'],
            'type_title'           => ['required', 'string'],
            'is_sale_bill_printed' => ['nullable', 'boolean'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
