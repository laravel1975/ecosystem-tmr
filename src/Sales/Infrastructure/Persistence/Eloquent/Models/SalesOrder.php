<?php

namespace TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMove;

class SalesOrder extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date_order' => 'date',
        'date_delivery_expected' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesOrderLine::class, 'order_id');
    }

    // เชื่อมโยงกับ Stock Move (Polymorphic) เพื่อดูว่า SO นี้สร้างใบส่งของอะไรบ้าง
    public function stockMoves(): MorphMany
    {
        return $this->morphMany(StockMove::class, 'reference');
    }
}
