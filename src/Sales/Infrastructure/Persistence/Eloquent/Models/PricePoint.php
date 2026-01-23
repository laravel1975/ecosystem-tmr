<?php

namespace TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryItem;

class PricePoint extends Model
{
    // ระบุชื่อตารางให้ตรงกับ Migration
    protected $table = 'sales_price_points';

    protected $fillable = [
        'inventory_item_id',
        'price_list_id',
        'amount',
        'currency',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'amount' => 'float',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * เชื่อมโยงไปยังสินค้าในโมดูล Inventory
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * เชื่อมโยงไปยังประเภทราคา
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }
}
