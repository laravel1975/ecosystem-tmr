<?php

namespace TmrEcosystem\Inventory\Application\DTOs;

use Spatie\LaravelData\Data;
use DateTime;

class StockMoveData extends Data
{
    public function __construct(
        public ?string $uuid,
        public int $item_id,
        public int $source_location_id,
        public int $destination_location_id,
        public float $quantity_demand,
        public float $quantity_done,
        public string $state,
        public ?string $batch_number,
        public ?DateTime $date_expected,
    ) {}
}
