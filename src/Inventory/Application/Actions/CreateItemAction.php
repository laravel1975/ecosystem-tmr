<?php

namespace TmrEcosystem\Inventory\Application\Actions;

use TmrEcosystem\Inventory\Application\DTOs\InventoryItemData;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use Illuminate\Support\Str;

class CreateItemAction
{
    public function execute(InventoryItemData $data): InventoryItem
    {
        return InventoryItem::create([
            'uuid' => Str::uuid(),
            'sku' => $data->sku,
            'name' => $data->name,
            'description' => $data->description,
            'category_id' => $data->category_id,
            'uom_id' => $data->uom_id,
            'cost' => $data->cost,
            'price' => $data->price,
            'type' => $data->type,
            'is_active' => $data->is_active,
        ]);
    }
}
