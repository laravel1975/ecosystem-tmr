<?php

namespace TmrEcosystem\Sales\Application\DTOs;

use Spatie\LaravelData\Data;

class PricePointData extends Data
{
    public function __construct(
        public int $inventory_item_id,
        public int $price_list_id,
        public float $amount,
        public string $currency = 'THB',
        public ?string $valid_from = null,
        public ?string $valid_to = null,
    ) {}
}
