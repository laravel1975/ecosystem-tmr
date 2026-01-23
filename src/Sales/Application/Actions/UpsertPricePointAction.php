<?php

namespace TmrEcosystem\Sales\Application\Actions;

use TmrEcosystem\Sales\Application\DTOs\PricePointData;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\PricePoint;

class UpsertPricePointAction
{
    public function execute(PricePointData $data): PricePoint
    {
        return PricePoint::updateOrCreate(
            [
                'inventory_item_id' => $data->inventory_item_id,
                'price_list_id' => $data->price_list_id,
            ],
            [
                'amount' => $data->amount,
                'currency' => $data->currency,
                'valid_from' => $data->valid_from,
                'valid_to' => $data->valid_to,
            ]
        );
    }
}
