<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class TransactionsResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $record_id,
        public readonly int $visit_id,
        public readonly int $document_id,
        public readonly float $amount,
        public readonly ?int $sold_item_id,
        public readonly ?string $sold_item_type,
        public readonly string $date,
        public readonly ExpenseDTO $expense,
        public readonly ?MasterDTO $master,
        public readonly ?AccountDTO $account,
        public readonly ?ClientDTO $client,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'             => ['required', 'integer'],
            'record_id'      => ['required', 'integer'],
            'visit_id'       => ['required', 'integer'],
            'document_id'    => ['required', 'integer'],
            'amount'         => ['required', 'numeric'],
            'sold_item_id'   => ['nullable', 'integer'],
            'sold_item_type' => ['nullable', 'string'],
            'date'           => ['required', 'string'],
            'expense'        => ['nullable', 'array'],
            'master'         => ['nullable', 'array'],
            'account'        => ['nullable', 'array'],
            'client'         => ['nullable', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'expense' => ExpenseDTO::class,
            'master'  => MasterDTO::class,
            'account' => AccountDTO::class,
            'client'  => ClientDTO::class,
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
