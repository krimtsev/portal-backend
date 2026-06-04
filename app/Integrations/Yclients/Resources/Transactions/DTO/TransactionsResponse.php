<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class TransactionsResponse extends BaseResponse
{
    public function __construct(
        public int $id,
        public int $document_id,
        public string $date,
        public float $amount,
        public string $comment,
        public string $last_change_date,
        public array $expense,

        /** Динамическая вложенность {} / [] */
        public array $master,
        public array $account,
        public array $client,
    ) {}

    public function getMasterId(): ?int
    {
        return $this->master['id'] ?? null;
    }

    public function getAccountId(): ?int
    {
        return $this->account['id'] ?? null;
    }

    protected static function getInputMapping(): array
    {
        return [
            'id'               => 'data.id',
            'document_id'      => 'data.document_id',
            'date'             => 'data.date',
            'amount'           => 'data.amount',
            'comment'          => 'data.comment',
            'last_change_date' => 'data.last_change_date',

            'expense_id'   => 'data.expense.id',

            'master'   => 'data.master',
            'account'  => 'data.account',
            'client'   => 'data.client',
        ];
    }
}
