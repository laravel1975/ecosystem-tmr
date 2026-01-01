<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryUom extends Model
{
    protected $table = 'inventory_uoms';
    protected $guarded = [];

    protected $casts = [
        'ratio' => 'decimal:5',
    ];
}
