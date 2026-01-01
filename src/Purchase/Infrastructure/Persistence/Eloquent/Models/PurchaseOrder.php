<?php

namespace TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;

class PurchaseOrder extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date_order' => 'date',
        'date_expected' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class, 'order_id');
    }

    // เชื่อมโยงกับ Stock Move (Polymorphic)
    // เพื่อให้รู้ว่า PO นี้สร้าง Move ID ไหนบ้าง
    public function stockMoves(): MorphMany
    {
        return $this->morphMany(StockMove::class, 'reference');
    }
}
