<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(InventoryUom::class);
    }

    public function stockQuants(): HasMany
    {
        return $this->hasMany(StockQuant::class, 'item_id');
    }
}
