<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItemImage extends Model
{
    protected $fillable = ['inventory_item_id', 'file_path', 'file_name', 'is_main', 'sort_order'];

    protected $casts = [
        'is_main' => 'boolean',
    ];
}
