<?php

namespace App\Integrations\Yclients\Resources\Comments\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class CommentsResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $salon_id,
        public readonly int $master_id,
        public readonly int $type,
        public readonly int $rating,
        public readonly string $text,
        public readonly string $date,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'        => ['required', 'integer'],
            'salon_id'  => ['required', 'integer'],
            'master_id' => ['required', 'integer'],
            'type'      => ['required', 'integer'],
            'rating'    => ['required', 'integer'],
            'text'      => ['nullable', 'string'],
            'date'      => ['required', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
