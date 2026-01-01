<?php

namespace TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryUom;

class PurchaseOrderLine extends Model
{
    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(InventoryUom::class, 'uom_id');
    }
}
