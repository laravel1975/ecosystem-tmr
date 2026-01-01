<?php

namespace TmrEcosystem\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $guarded = [];
    // หรือ protected $fillable = ['uuid', 'name', 'code', 'usage', 'parent_id', 'parent_path', 'is_scrap', ...];
}
