<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class GoodsTransactionDTO extends ValidateResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $barcode,
        public readonly ?string $article,
        public readonly int $amount,
        public readonly float $cost_per_unit,
        public readonly float $price,
        public readonly float $cost,
        public readonly float $cost_to_pay,
        public readonly float $manual_cost,
        public readonly int $master_id,
        public readonly int $storage_id,
        public readonly int $good_id,
        public readonly float $discount,
        public readonly int $loyalty_abonement_id,
        public readonly int $loyalty_certificate_id,
        public readonly ?string $good_special_number,
    ) {}

    protected static function rules(): array
    {
        return [
            'id'                     => ['required', 'integer'],
            'title'                  => ['required', 'string'],
            'barcode'                => ['nullable', 'string'],
            'article'                => ['nullable', 'string'],
            'amount'                 => ['required', 'integer'],
            'cost_per_unit'          => ['required', 'numeric'],
            'price'                  => ['required', 'numeric'],
            'cost'                   => ['required', 'numeric'],
            'cost_to_pay'            => ['required', 'numeric'],
            'manual_cost'            => ['required', 'numeric'],
            'master_id'              => ['required', 'integer'],
            'storage_id'             => ['required', 'integer'],
            'good_id'                => ['required', 'integer'],
            'discount'               => ['required', 'numeric'],
            'loyalty_abonement_id'   => ['required', 'integer'],
            'loyalty_certificate_id' => ['required', 'integer'],
            'good_special_number'    => ['nullable', 'string'],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
