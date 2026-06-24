<?php

namespace App\Integrations\Yclients\Resources\StorageTransactions\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Поиск товарных транзакций
 * $id - Идентификатор транзакции
 * $document_id - Идентификатор документа
 * $type_id - Идентификатор типа транзакции
 * $type - Тип транзакции
 * $operation_unit_type - Тип единицы измерения: 1 - для продажи, 2 - для списания
 * $amount
 * $create_date - Дата создания
 * $cost_per_unit - Цена за единицу
 * $cost - Цена
 * $discount - Скидка
 * $comment
 * $record_id - Идентификатор записи
 * $loyalty_abonement_id
 * $loyalty_certificate_id
 * $good - Товар
 * $unit - Единица измерения
 * $storage - Склад
 * $client - Клиент
 * $master - Сотрудник
 * $service - Услуга
 * $supplier - Поставщик
 */
final class StorageTransactionsResponse extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $document_id,
        public readonly int $type_id,
        public readonly string $type,
        public readonly int $operation_unit_type,
        public readonly float $amount,
        public readonly string $create_date,
        public readonly float $cost_per_unit,
        public readonly float $cost,
        public readonly float $discount,
        public readonly string $comment,
        public readonly int $record_id,
        public readonly string $last_change_date,
        public readonly int $loyalty_abonement_id,
        public readonly int $loyalty_certificate_id,
        public readonly GoodDTO $good,
        public readonly UnitDTO $unit,
        public readonly StorageDTO $storage,
        public readonly ?ClientDTO $client = null,
        public readonly ?MasterDTO $master = null,
        public readonly ?ServiceDTO $service = null,
        public readonly ?SupplierDTO $supplier = null,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'                     => ['required', 'integer'],
            'document_id'            => ['required', 'integer'],
            'record_id'              => ['required', 'integer'],
            'type_id'                => ['required', 'integer'],
            'type'                   => ['required', 'string'],
            'operation_unit_type'    => ['required', 'integer'],
            'amount'                 => ['required', 'numeric'],
            'create_date'            => ['required', 'string'],
            'cost_per_unit'          => ['required', 'numeric'],
            'cost'                   => ['required', 'numeric'],
            'discount'               => ['required', 'numeric'],
            'comment'                => ['nullable', 'string'],
            'last_change_date'       => ['required', 'string'],
            'loyalty_abonement_id'   => ['required', 'integer'],
            'loyalty_certificate_id' => ['required', 'integer'],
            'good'                   => ['required', 'array'],
            'unit'                   => ['required', 'array'],
            'storage'                => ['required', 'array'],
            'client'                 => ['nullable', 'array'],
            'master'                 => ['nullable', 'array'],
            'service'                => ['nullable', 'array'],
            'supplier'               => ['nullable', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'good'     => GoodDTO::class,
            'unit'     => UnitDTO::class,
            'storage'  => StorageDTO::class,
            'client'   => ClientDTO::class,
            'master'   => MasterDTO::class,
            'service'  => ServiceDTO::class,
            'supplier' => SupplierDTO::class,
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
