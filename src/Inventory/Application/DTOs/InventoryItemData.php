<?php

namespace TmrEcosystem\Inventory\Application\DTOs;

use Spatie\LaravelData\Data;

class InventoryItemData extends Data
{
    public function __construct(
        public ?int $id,
        public string $sku,
        public string $name,
        public ?string $description,
        public float $cost,
        public float $price,
        public string $type,
        public ?int $category_id,
        public int $uom_id,
        public bool $is_active,
    ) {}
}
