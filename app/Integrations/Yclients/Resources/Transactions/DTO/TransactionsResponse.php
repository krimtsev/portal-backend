<?php

namespace App\Integrations\Yclients\Resources\Transactions\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class TransactionsResponse extends BaseResponse
{
    /** @var ExpenseDTO|null */
    private ?ExpenseDTO $_expense = null;

    /** @var MasterDTO|null */
    private ?MasterDTO $_master = null;

    /** @var AccountDTO|null */
    private ?AccountDTO $_account = null;

    /** @var ClientDTO|null */
    private ?ClientDTO $_client = null;

    public function __construct(
        public int $id,
        public int $record_id,
        public int $visit_id,
        public int $document_id,
        public float $amount,
        public int $sold_item_id,
        public ?string $sold_item_type,
        public string $date,
        public array $expense,
        public array $master,
        public array $account,
        public array $client,
    ) {}

    public function expense(): ExpenseDTO
    {
        if ($this->_expense === null) {
            $this->_expense = new ExpenseDTO(...$this->expense);
        }
        return $this->_expense;
    }

    public function master(): MasterDTO
    {
        if ($this->_master === null) {
            $this->_master = new MasterDTO(...$this->master);
        }
        return $this->_master;
    }

    public function account(): AccountDTO
    {
        if ($this->_account === null) {
            $this->_account = new AccountDTO(...$this->account);
        }
        return $this->_account;
    }

    public function client(): ClientDTO
    {
        if ($this->_client === null) {
            $this->_client = new ClientDTO(...$this->client);
        }
        return $this->_client;
    }

    protected static function getInputMapping(): array
    {
        return [
            'id'             => 'data.id',
            'record_id'      => 'data.record_id',
            'visit_id'       => 'data.visit_id',
            'document_id'    => 'data.document_id',
            'amount'         => 'data.amount',
            'sold_item_id'   => 'data.sold_item_id',
            'sold_item_type' => 'data.sold_item_type',
            'date'           => 'data.date',
            'expense'        => 'data.expense',
            'master'         => 'data.master',
            'account'        => 'data.account',
            'client'         => 'data.client',
        ];
    }
}
